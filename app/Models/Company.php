<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address_line1',
        'address_line2',
        'city',
        'postal_code',
        'country',
        'vat_number',
        'registration_number',
        'bank_name',
        'bank_iban',
        'bank_bic',
        'default_currency',
        'default_payment_terms',
        'default_invoice_notes',
        'invoice_logo',
        'invoice_prefix',
        'invoice_template',
        'invoice_starting_number',
        'invoice_numbering_format',
        'bg_invoice_sequence_start',
        'issued_by_default_name',
        'vat_exempt',
        'vat_exempt_reason',
        'vat_exempt_notice_language',
        'custom_invoice_rules',
    ];

    protected function casts(): array
    {
        return [
            'vat_exempt' => 'boolean',
            'custom_invoice_rules' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function defaultPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true);
    }

    public function logoUrl(): ?string
    {
        if (! $this->invoice_logo) {
            return null;
        }

        return Storage::disk('minio')->url($this->invoice_logo);
    }
}
