<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;

    public const TYPE_BANK_TRANSFER = 'bank_transfer';

    public const TYPE_STRIPE = 'stripe';

    public const TYPE_CASH = 'cash';

    public const TYPES = [
        self::TYPE_BANK_TRANSFER,
        self::TYPE_STRIPE,
        self::TYPE_CASH,
    ];

    protected $fillable = [
        'company_id',
        'type',
        'label',
        'is_default',
        'bank_name',
        'bank_iban',
        'bank_bic',
        'stripe_connect_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Return a display-friendly label for this payment method.
     */
    public function displayLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return match ($this->type) {
            self::TYPE_BANK_TRANSFER => $this->bank_name ?: __('Bank Transfer'),
            self::TYPE_STRIPE => 'Stripe',
            self::TYPE_CASH => __('Cash'),
            default => $this->type,
        };
    }

    /**
     * Return a snapshot array suitable for storing on an invoice.
     *
     * @return array<string, mixed>
     */
    public function toSnapshot(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->displayLabel(),
            'bank_name' => $this->bank_name,
            'bank_iban' => $this->bank_iban,
            'bank_bic' => $this->bank_bic,
            'notes' => $this->notes,
        ];
    }
}
