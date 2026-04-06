<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentImport extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentImportFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'batch_id',
        'original_filename',
        'stored_path',
        'mime_type',
        'document_type',
        'status',
        'extracted_data',
        'error_message',
        'invoice_id',
        'expense_id',
        'used_own_key',
    ];

    protected function casts(): array
    {
        return [
            'extracted_data' => 'array',
            'used_own_key' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isExtracted(): bool
    {
        return $this->status === 'extracted';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
