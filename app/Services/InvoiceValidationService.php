<?php

namespace App\Services;

use App\DataTransferObjects\ValidationResult;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;

class InvoiceValidationService
{
    public function validate(Invoice $invoice, Company $company): ValidationResult
    {
        $countryCode = strtoupper($company->country ?? 'BG');

        // Proformas skip strict validation
        if ($invoice->document_type === 'proforma') {
            return new ValidationResult;
        }

        $allConfig = require base_path('config/invoice_validation.php');
        $config = $allConfig[$countryCode]['legal_requirements'] ?? [];

        $errors = [];
        $warnings = [];

        $invoice->loadMissing(['items', 'client']);

        // Field presence checks
        $fieldMap = $this->resolveFieldValues($invoice, $company);

        foreach ($config as $field => $rule) {
            // Skip line_items.* rules — handled separately
            if (str_starts_with($field, 'line_items.') || $field === 'line_items') {
                continue;
            }

            $ruleSpec = is_array($rule) ? $rule : ['rule' => $rule];
            $isRequired = str_contains($ruleSpec['rule'], 'required');

            if (! $isRequired) {
                continue;
            }

            // Check conditions
            if (isset($ruleSpec['condition']) && ! $this->evaluateCondition($ruleSpec['condition'], $invoice)) {
                continue;
            }

            $value = $fieldMap[$field] ?? null;

            if ($this->isEmpty($value)) {
                $errors[] = [
                    'field' => $field,
                    'message' => $this->fieldLabel($field).' is required.',
                    'legal_ref' => null,
                ];
            }
        }

        // Line items check
        if ($invoice->items->isEmpty()) {
            $errors[] = [
                'field' => 'line_items',
                'message' => 'At least one line item is required.',
                'legal_ref' => null,
            ];
        } else {
            foreach ($invoice->items as $index => $item) {
                if ($this->isEmpty($item->description)) {
                    $errors[] = ['field' => "line_items.{$index}.description", 'message' => 'Line item description is required.', 'legal_ref' => null];
                }
                if ($this->isEmpty($item->quantity) || (float) $item->quantity <= 0) {
                    $errors[] = ['field' => "line_items.{$index}.quantity", 'message' => 'Line item quantity must be greater than 0.', 'legal_ref' => null];
                }
                if ($this->isEmpty($item->unit)) {
                    $errors[] = ['field' => "line_items.{$index}.unit", 'message' => 'Line item unit is required.', 'legal_ref' => null];
                }
                if ($this->isEmpty($item->unit_price)) {
                    $errors[] = ['field' => "line_items.{$index}.unit_price", 'message' => 'Line item unit price is required.', 'legal_ref' => null];
                }
            }
        }

        // Merge custom company rules
        $customResult = $this->validateCustomRules($invoice, $company);

        return (new ValidationResult($errors, $warnings))->merge($customResult);
    }

    public function canIssue(Invoice $invoice, Company $company): bool
    {
        return $this->validate($invoice, $company)->passes();
    }

    public function clientCompleteness(Client $client, string $sellerCountry): ValidationResult
    {
        $errors = [];

        if ($this->isEmpty($client->name)) {
            $errors[] = ['field' => 'buyer_name', 'message' => 'Client name is required.', 'legal_ref' => null];
        }

        if ($this->isEmpty($client->address)) {
            $errors[] = ['field' => 'buyer_address', 'message' => 'Client address is required.', 'legal_ref' => null];
        }

        // BG buyer needs EIK or VAT
        if (strtoupper($client->country ?? '') === 'BG') {
            if ($this->isEmpty($client->vat_number) && $this->isEmpty($client->registration_number)) {
                $errors[] = [
                    'field' => 'buyer_eik_or_vat',
                    'message' => 'Bulgarian clients require an EIK or VAT number.',
                    'legal_ref' => null,
                ];
            }
        }

        // EU business buyer needs VAT number for reverse charge
        $euService = new EuVatService;
        if ($euService->isEuCountry($client->country ?? '') && strtoupper($client->country ?? '') !== strtoupper($sellerCountry)) {
            if ($this->isEmpty($client->vat_number)) {
                // Warning, not error — could be a consumer
                return new ValidationResult($errors, [
                    ['field' => 'buyer_vat_number', 'message' => 'Missing VAT number — required for reverse charge invoices to EU businesses.'],
                ]);
            }
        }

        return new ValidationResult($errors);
    }

    private function resolveFieldValues(Invoice $invoice, Company $company): array
    {
        $client = $invoice->client;

        return [
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date,
            'tax_event_date' => $invoice->tax_event_date,
            'seller_name' => $company->name,
            'seller_address' => $company->address_line1,
            'seller_eik_or_vat' => $company->vat_number ?? $company->registration_number,
            'buyer_name' => $client?->name,
            'buyer_address' => $client?->address,
            'buyer_eik_or_vat' => $client?->vat_number ?? $client?->registration_number,
            'vat_legal_basis' => $invoice->vat_legal_basis,
            'issued_by_name' => $invoice->issued_by_name,
            'received_by_name' => $invoice->received_by_name,
            'payment_method' => $invoice->payment_method_id ?? ($invoice->payment_method_snapshot ? true : null),
            'payment_due_date' => $invoice->payment_due_date,
            'original_invoice_id' => $invoice->original_invoice_id,
            'correction_reason' => $invoice->correction_reason,
        ];
    }

    private function evaluateCondition(string $condition, Invoice $invoice): bool
    {
        return match ($condition) {
            'buyer_country_is_bg' => strtoupper($invoice->client?->country ?? '') === 'BG',
            'has_zero_rate_items' => $invoice->items->contains(fn ($item) => (float) $item->vat_rate === 0.0),
            'is_credit_or_debit_note' => in_array($invoice->document_type, ['credit_note', 'debit_note']),
            default => true,
        };
    }

    private function validateCustomRules(Invoice $invoice, Company $company): ValidationResult
    {
        $custom = $company->custom_invoice_rules;
        if (empty($custom) || empty($custom['required_fields'])) {
            return new ValidationResult;
        }

        $errors = [];
        $labels = $custom['field_labels'] ?? [];

        foreach ($custom['required_fields'] as $field) {
            $value = $invoice->{$field} ?? null;
            if ($this->isEmpty($value)) {
                $label = $labels[$field] ?? $field;
                $errors[] = [
                    'field' => $field,
                    'message' => "{$label} is required (company rule).",
                    'legal_ref' => null,
                ];
            }
        }

        return new ValidationResult($errors);
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === false;
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'invoice_number' => 'Invoice number',
            'issue_date' => 'Issue date',
            'tax_event_date' => 'Tax event date',
            'seller_name' => 'Seller name',
            'seller_address' => 'Seller address',
            'seller_eik_or_vat' => 'Seller EIK/VAT number',
            'buyer_name' => 'Buyer name',
            'buyer_address' => 'Buyer address',
            'buyer_eik_or_vat' => 'Buyer EIK/VAT number',
            'vat_legal_basis' => 'VAT legal basis',
            'issued_by_name' => 'Issued by',
            'received_by_name' => 'Received by',
            'payment_method' => 'Payment method',
            'payment_due_date' => 'Payment due date',
            'original_invoice_id' => 'Original invoice reference',
            'correction_reason' => 'Correction reason',
            default => str_replace('_', ' ', ucfirst($field)),
        };
    }
}
