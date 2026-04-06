<?php

namespace App\Services;

use App\Exceptions\NoAvailableApiKeyException;
use App\Models\AiApiKey;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GeminiExtractionService
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly AiKeyRotationService $rotation,
    ) {}

    /**
     * Extract structured data from a document file.
     *
     * @param  string  $storedPath  MinIO path of the uploaded file
     * @param  string  $mimeType  MIME type (image/jpeg, image/png, application/pdf)
     * @param  string  $documentType  'invoice' or 'expense'
     * @param  User|null  $user  When provided, uses the user's own API key if configured
     * @return array{data: array<string, mixed>, usedOwnKey: bool}
     *
     * @throws \RuntimeException
     */
    public function extractFromDocument(string $storedPath, string $mimeType, string $documentType, ?User $user = null): array
    {
        $fileContents = Storage::disk('minio')->get($storedPath);

        if ($fileContents === null) {
            throw new \RuntimeException("File not found at path: {$storedPath}");
        }

        $base64 = base64_encode($fileContents);
        $prompt = $this->buildPrompt($documentType);
        $schema = $this->buildResponseSchema($documentType);

        if ($user?->gemini_api_key) {
            $result = $this->callGeminiWithRawKey($user->gemini_api_key, $base64, $mimeType, $prompt, $schema);

            return ['data' => $result, 'usedOwnKey' => true];
        }

        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $key = $this->rotation->getNextKey();
                $result = $this->callGemini($key, $base64, $mimeType, $prompt, $schema);
                $this->rotation->markUsed($key);

                return ['data' => $result, 'usedOwnKey' => false];
            } catch (NoAvailableApiKeyException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $lastError = $e;

                if (isset($key)) {
                    $this->rotation->markFailed($key, $this->extractErrorMessage($e));
                }

                if ($attempt === self::MAX_ATTEMPTS) {
                    break;
                }
            }
        }

        throw new \RuntimeException(
            'Failed to extract document data after '.self::MAX_ATTEMPTS.' attempts: '.$lastError?->getMessage(),
            0,
            $lastError,
        );
    }

    /**
     * Test that an AiApiKey model is valid by sending a minimal request.
     *
     * @throws \RuntimeException
     */
    public function testKey(AiApiKey $key): void
    {
        $this->testRawKey($key->api_key);
    }

    /**
     * Test that a raw API key string is valid by sending a minimal request.
     *
     * @throws \RuntimeException
     */
    public function testRawKey(string $apiKey): void
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Reply with: {"ok": true}'],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
            ],
        ];

        $endpoint = config('ai.gemini.endpoint');
        $response = Http::timeout(15)
            ->post("{$endpoint}?key={$apiKey}", $payload);

        if ($response->failed()) {
            throw new \RuntimeException($this->parseApiError($response->json()));
        }
    }

    /** @return array<string, mixed> */
    private function callGemini(AiApiKey $key, string $base64, string $mimeType, string $prompt, array $schema): array
    {
        $payload = $this->buildPayload($base64, $mimeType, $prompt, $schema);
        $endpoint = config('ai.gemini.endpoint');

        try {
            $response = Http::timeout(90)
                ->post("{$endpoint}?key={$key->api_key}", $payload);
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Connection to Gemini API timed out.', 0, $e);
        }

        if ($response->status() === 429) {
            throw new \RuntimeException('Rate limit exceeded (429).');
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new \RuntimeException('Invalid or unauthorized API key ('.$response->status().').');
        }

        if ($response->failed()) {
            $error = $this->parseApiError($response->json());
            throw new \RuntimeException("Gemini API error: {$error}");
        }

        $json = $response->json();
        $text = data_get($json, 'candidates.0.content.parts.0.text', '');

        if (empty($text)) {
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Gemini JSON response: '.json_last_error_msg());
        }

        return $decoded ?? [];
    }

    /** @return array<string, mixed> */
    private function callGeminiWithRawKey(string $apiKey, string $base64, string $mimeType, string $prompt, array $schema): array
    {
        $payload = $this->buildPayload($base64, $mimeType, $prompt, $schema);
        $endpoint = config('ai.gemini.endpoint');

        try {
            $response = Http::timeout(90)
                ->post("{$endpoint}?key={$apiKey}", $payload);
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Connection to Gemini API timed out.', 0, $e);
        }

        if ($response->status() === 429) {
            throw new \RuntimeException('Rate limit exceeded (429).');
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new \RuntimeException('Invalid or unauthorized API key ('.$response->status().').');
        }

        if ($response->failed()) {
            $error = $this->parseApiError($response->json());
            throw new \RuntimeException("Gemini API error: {$error}");
        }

        $json = $response->json();
        $text = data_get($json, 'candidates.0.content.parts.0.text', '');

        if (empty($text)) {
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Gemini JSON response: '.json_last_error_msg());
        }

        return $decoded ?? [];
    }

    private function buildPrompt(string $documentType): string
    {
        return match ($documentType) {
            'invoice' => <<<'PROMPT'
You are an invoice data extraction assistant. Analyze the provided document and extract all invoice data.
Return ONLY a valid JSON object with no additional text. Extract every field you can find.
For dates, use ISO format YYYY-MM-DD. For monetary amounts, use numeric values without currency symbols.
If a field is not found in the document, use null. For line_items, extract each item as a separate object.
Detect the currency from symbols or text (EUR, USD, GBP, BGN, etc.) and return the ISO 4217 code.
PROMPT,
            'expense' => <<<'PROMPT'
You are a receipt and expense data extraction assistant. Analyze the provided document and extract all relevant data.
Return ONLY a valid JSON object with no additional text.
For dates, use ISO format YYYY-MM-DD. For monetary amounts, use numeric values without currency symbols.
For category, map to one of: software, hardware, travel, hosting, marketing, other.
If a field is not found, use null.
PROMPT,
            default => throw new \InvalidArgumentException("Unknown document type: {$documentType}"),
        };
    }

    /** @return array<string, mixed> */
    private function buildResponseSchema(string $documentType): array
    {
        return match ($documentType) {
            'invoice' => [
                'type' => 'OBJECT',
                'properties' => [
                    'vendor_name' => ['type' => 'STRING'],
                    'vendor_address' => ['type' => 'STRING'],
                    'vendor_vat_number' => ['type' => 'STRING'],
                    'client_name' => ['type' => 'STRING'],
                    'client_address' => ['type' => 'STRING'],
                    'client_vat_number' => ['type' => 'STRING'],
                    'invoice_number' => ['type' => 'STRING'],
                    'issue_date' => ['type' => 'STRING'],
                    'due_date' => ['type' => 'STRING'],
                    'currency' => ['type' => 'STRING'],
                    'subtotal' => ['type' => 'NUMBER'],
                    'vat_amount' => ['type' => 'NUMBER'],
                    'total' => ['type' => 'NUMBER'],
                    'notes' => ['type' => 'STRING'],
                    'line_items' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'description' => ['type' => 'STRING'],
                                'quantity' => ['type' => 'NUMBER'],
                                'unit_price' => ['type' => 'NUMBER'],
                                'vat_rate' => ['type' => 'NUMBER'],
                            ],
                        ],
                    ],
                ],
            ],
            'expense' => [
                'type' => 'OBJECT',
                'properties' => [
                    'vendor_name' => ['type' => 'STRING'],
                    'description' => ['type' => 'STRING'],
                    'amount' => ['type' => 'NUMBER'],
                    'currency' => ['type' => 'STRING'],
                    'category' => ['type' => 'STRING'],
                    'date' => ['type' => 'STRING'],
                    'vat_amount' => ['type' => 'NUMBER'],
                ],
            ],
            default => throw new \InvalidArgumentException("Unknown document type: {$documentType}"),
        };
    }

    /** @return array<string, mixed> */
    private function buildPayload(string $base64, string $mimeType, string $prompt, array $schema): array
    {
        return [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'response_schema' => $schema,
                'temperature' => 0.1,
            ],
        ];
    }

    private function extractErrorMessage(\Throwable $e): string
    {
        return substr($e->getMessage(), 0, 500);
    }

    /** @param array<string, mixed>|null $json */
    private function parseApiError(?array $json): string
    {
        return data_get($json, 'error.message', 'Unknown API error');
    }
}
