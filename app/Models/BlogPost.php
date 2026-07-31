<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'category_id',
        'title',
        'slug',
        'body',
        'featured_image',
        'thumbnail_emoji',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BlogPost $post): void {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->body), 160);
    }

    public function getThumbnailAttribute(): string
    {
        return $this->thumbnail_emoji ?: '📝';
    }

    public function getPreviewAttribute(): string
    {
        preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $this->body, $matches);
        $paragraphs = array_slice($matches[1] ?? [], 0, 2);

        if (empty($paragraphs)) {
            return Str::limit(strip_tags($this->body), 600);
        }

        $text = collect($paragraphs)
            ->map(fn (string $paragraph): string => trim(strip_tags($paragraph)))
            ->filter()
            ->implode("\n\n");

        return Str::limit($text, 700);
    }

    public static function flagEmoji(string $isoCode): string
    {
        return collect(str_split(strtoupper($isoCode)))
            ->map(fn (string $letter): string => mb_chr(0x1F1E6 + (ord($letter) - ord('A'))))
            ->implode('');
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($words / 200));
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }
}
