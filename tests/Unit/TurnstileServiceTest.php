<?php

namespace Tests\Unit;

use App\Services\TurnstileService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileServiceTest extends TestCase
{
    private TurnstileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TurnstileService;
    }

    public function test_verify_returns_true_on_success(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->assertTrue($this->service->verify('good-token', '127.0.0.1'));
    }

    public function test_verify_returns_false_when_cloudflare_rejects_the_token(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response([
            'success' => false,
            'error-codes' => ['invalid-input-response'],
        ])]);

        $this->assertFalse($this->service->verify('bad-token', '127.0.0.1'));
    }

    public function test_verify_returns_false_on_non_2xx_response(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true], 500)]);

        $this->assertFalse($this->service->verify('good-token', '127.0.0.1'));
    }

    public function test_verify_fails_closed_on_connection_exception(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Could not connect');
        });

        $this->assertFalse($this->service->verify('good-token', '127.0.0.1'));
    }

    public function test_verify_sends_the_configured_secret_and_the_given_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->service->verify('the-token', '203.0.113.5');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && $request['secret'] === 'test-secret'
                && $request['response'] === 'the-token'
                && $request['remoteip'] === '203.0.113.5';
        });
    }
}
