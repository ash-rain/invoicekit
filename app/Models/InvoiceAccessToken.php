<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InvoiceAccessToken extends Model
{
    protected $fillable = [
        'invoice_id',
        'token',
        'password_hash',
        'expires_at',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accessed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPasswordProtected(): bool
    {
        return $this->password_hash !== null;
    }

    public static function generateFor(Invoice $invoice, ?string $password = null, ?\DateTimeInterface $expiresAt = null): self
    {
        return self::create([
            'invoice_id' => $invoice->id,
            'token' => Str::random(48),
            'password_hash' => $password ? bcrypt($password) : null,
            'expires_at' => $expiresAt,
        ]);
    }
}
