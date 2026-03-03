<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'description',
        'started_at',
        'stopped_at',
        'duration_minutes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isRunning(): bool
    {
        return $this->stopped_at === null;
    }

    public function stop(): void
    {
        $this->stopped_at = now();
        $this->duration_minutes = (int) $this->started_at->diffInMinutes($this->stopped_at);
        $this->save();
    }
}
