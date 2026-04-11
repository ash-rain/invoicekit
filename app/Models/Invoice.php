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
        'vat_legal_basis',
        'stripe_payment_link_url',
        'template',
        'document_type',
        'tax_event_date',
        'issued_by_name',
        'received_by_name',
        'original_invoice_id',
        'cancellation_reason',
        'cancelled_at',
        'vat_amount_bgn',
        'payment_method_id',
        'payment_method_snapshot',
        'vat_summary',
        'payment_due_date',
        'correction_reason',
        'original_invoice_number',
        'original_invoice_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'tax_event_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'vat_amount_bgn' => 'decimal:2',
        'vat_exempt_applied' => 'boolean',
        'payment_method_snapshot' => 'array',
        'payment_due_date' => 'date',
        'original_invoice_date' => 'date',
        'vat_summary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Resolve the payment method for display: snapshot first, then live method, then company default.
     *
     * @return array<string, mixed>|null
     */
    public function resolvedPaymentMethod(): ?array
    {
        if ($this->payment_method_snapshot) {
            return $this->payment_method_snapshot;
        }

        $method = $this->paymentMethod;
        if ($method) {
            return $method->toSnapshot();
        }

        $company = $this->user?->currentCompany;
        $default = $company?->defaultPaymentMethod;
        if ($default) {
            return $default->toSnapshot();
        }

        return null;
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

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    public function isProforma(): bool
    {
        return $this->document_type === 'proforma';
    }

    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id')->where('document_type', 'credit_note');
    }

    public function debitNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id')->where('document_type', 'debit_note');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Generate the next invoice number, delegating to the correct format
     * based on the company's invoice_numbering_format setting.
     */
    public static function generateNumber(int $userId, ?Company $company = null): string
    {
        if ($company?->invoice_numbering_format === 'bg_sequential') {
            return static::generateBulgarianNumber($userId, $company);
        }

        return static::generateStandardNumber($userId, $company);
    }

    /**
     * Generate the next invoice number in the format {PREFIX}-YYYY-NNNN.
     */
    public static function generateStandardNumber(int $userId, ?Company $company = null): string
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

    /**
     * Generate a 10-digit zero-padded sequential number as required by Bulgarian law
     * (ЗДДС Art. 114, para 1, item 2). Numbers are continuous across years and
     * shared across all document types (invoices, credit notes, debit notes).
     */
    public static function generateBulgarianNumber(int $userId, ?Company $company = null): string
    {
        $startingNumber = $company?->bg_invoice_sequence_start ?? 1;

        $last = static::where('user_id', $userId)
            ->whereIn('document_type', ['invoice', 'credit_note', 'debit_note'])
            ->whereNotNull('invoice_number')
            ->orderByDesc('id')
            ->value('invoice_number');

        if ($last && ctype_digit($last)) {
            $next = (int) $last + 1;
        } else {
            $next = $startingNumber;
        }

        return str_pad((string) $next, 10, '0', STR_PAD_LEFT);
    }
}
