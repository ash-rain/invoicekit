<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'notes',
        'paid_at',
        'vat_type',
        'language',
        'vat_exempt_applied',
        'vat_exempt_notice',
        'stripe_payment_link_url',
        'template',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'vat_exempt_applied' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(InvoiceAccessToken::class);
    }

    public function generatePortalLink(?string $password = null, ?\DateTimeInterface $expiresAt = null): InvoiceAccessToken
    {
        return InvoiceAccessToken::generateFor($this, $password, $expiresAt);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Generate the next invoice number in the format {PREFIX}-YYYY-NNNN.
     */
    public static function generateNumber(int $userId, ?Company $company = null): string
    {
        $year = now()->year;
        $rawPrefix = $company?->invoice_prefix ?: 'INV';
        $prefix = "{$rawPrefix}-{$year}-";
        $startingNumber = $company?->invoice_starting_number ?? 1;

        $last = static::where('user_id', $userId)
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : $startingNumber;

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
