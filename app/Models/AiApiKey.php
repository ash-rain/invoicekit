<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiApiKey extends Model
{
    /** @use HasFactory<\Database\Factories\AiApiKeyFactory> */
    use HasFactory;

    protected $fillable = [
        'provider',
        'api_key',
        'label',
        'is_active',
        'request_count',
        'last_used_at',
        'last_error_at',
        'last_error_message',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'request_count' => 'integer',
            'last_used_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->where(function (Builder $q) {
            $q->whereNull('last_error_at')
                ->orWhere('last_error_at', '<', now()->subSeconds(60));
        });
    }
}
