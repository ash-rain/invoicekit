<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'frequency',
        'next_send_date',
        'last_sent_date',
        'currency',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'vat_type',
        'language',
        'notes',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'next_send_date' => 'date',
            'last_sent_date' => 'date',
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

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
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function generateInvoice(): Invoice
    {
        $invoice = Invoice::create([
            'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'invoice_number' => Invoice::generateNumber($this->user_id, $this->user?->currentCompany),
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'total' => $this->total,
            'vat_type' => $this->vat_type,
            'language' => $this->language,
            'notes' => $this->notes,
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_rate' => $this->vat_rate,
                'total' => round((float) $item->quantity * (float) $item->unit_price, 2),
            ]);
        }

        $this->update([
            'last_sent_date' => now()->toDateString(),
            'next_send_date' => $this->nextDateAfter(now()),
        ]);

        return $invoice;
    }

    public function nextDateAfter(\DateTimeInterface $from): Carbon
    {
        return match ($this->frequency) {
            'quarterly' => Carbon::instance($from)->addMonths(3),
            'annually' => Carbon::instance($from)->addYear(),
            default => Carbon::instance($from)->addMonth(),
        };
    }
}
