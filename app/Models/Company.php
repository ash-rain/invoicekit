<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'vat_exempt',
        'vat_exempt_reason',
        'vat_exempt_notice_language',
    ];

    protected function casts(): array
    {
        return [
            'vat_exempt' => 'boolean',
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

    public function logoUrl(): ?string
    {
        if (! $this->invoice_logo) {
            return null;
        }

        return Storage::disk('minio')->url($this->invoice_logo);
    }
}
