<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'admin_id' => Admin::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'featured_image' => null,
            'meta_title' => null,
            'meta_description' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'published_at' => null,
        ]);
    }
}
