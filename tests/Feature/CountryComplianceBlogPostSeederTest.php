<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Database\Seeders\CountryComplianceBlogPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryComplianceBlogPostSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_compliance_and_news_categories(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $this->assertTrue(BlogCategory::where('slug', 'compliance')->exists());
        $this->assertTrue(BlogCategory::where('slug', 'news')->exists());
    }

    public function test_all_twenty_seven_posts_belong_to_compliance_category(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $compliance = BlogCategory::where('slug', 'compliance')->firstOrFail();

        $this->assertSame(27, BlogPost::where('category_id', $compliance->id)->count());
    }

    public function test_seeding_twice_does_not_duplicate_categories(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $this->assertSame(2, BlogCategory::count());
    }

    private const BUCKET_B_SLUGS = [
        'belgium-compliance-status',
        'croatia-compliance-status',
        'bulgaria-compliance-status',
        'greece-compliance-status',
        'italy-compliance-status',
        'poland-compliance-status',
        'spain-compliance-status',
        'france-compliance-status',
        'romania-compliance-status',
    ];

    public function test_seeding_creates_exactly_twenty_seven_blog_posts(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $this->assertSame(27, BlogPost::count());
    }

    public function test_seeding_twice_does_not_create_duplicates(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $this->assertSame(27, BlogPost::count());
    }

    public function test_belgium_title_contains_seventy_five_percent(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $belgium = BlogPost::where('slug', 'belgium-compliance-status')->firstOrFail();

        $this->assertStringContainsString('75%', $belgium->title);
    }

    public function test_spain_title_contains_forty_percent(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $spain = BlogPost::where('slug', 'spain-compliance-status')->firstOrFail();

        $this->assertStringContainsString('40%', $spain->title);
    }

    public function test_austria_title_does_not_contain_percent_sign(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $austria = BlogPost::where('slug', 'austria-compliance-status')->firstOrFail();

        $this->assertStringNotContainsString('%', $austria->title);
    }

    public function test_austria_has_correct_flag_emoji(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $austria = BlogPost::where('slug', 'austria-compliance-status')->firstOrFail();

        $this->assertSame(BlogPost::flagEmoji('AT'), $austria->thumbnail_emoji);
    }

    public function test_every_seeded_post_has_a_two_codepoint_flag_emoji(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        foreach (BlogPost::all() as $post) {
            $this->assertNotNull($post->thumbnail_emoji, "Expected {$post->slug} to have a thumbnail_emoji.");
            $this->assertSame(2, mb_strlen($post->thumbnail_emoji), "Expected {$post->slug}'s flag emoji to be 2 codepoints.");
        }
    }

    public function test_every_seeded_post_has_meta_title_and_meta_description_within_length_limits(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        foreach (BlogPost::all() as $post) {
            $this->assertNotEmpty($post->meta_title, "Expected {$post->slug} to have a meta_title.");
            $this->assertLessThanOrEqual(255, mb_strlen($post->meta_title), "Expected {$post->slug}'s meta_title to fit the 255-char limit.");

            $this->assertNotEmpty($post->meta_description, "Expected {$post->slug} to have a meta_description.");
            $this->assertLessThanOrEqual(320, mb_strlen($post->meta_description), "Expected {$post->slug}'s meta_description to fit the 320-char limit.");
        }
    }

    public function test_austria_meta_title_mentions_austria_and_vat(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        $austria = BlogPost::where('slug', 'austria-compliance-status')->firstOrFail();

        $this->assertStringContainsString('Austria', $austria->meta_title);
        $this->assertStringContainsString('VAT', $austria->meta_title);
    }

    public function test_every_bucket_b_post_body_clarifies_percentage_is_not_a_certification(): void
    {
        $this->seed(CountryComplianceBlogPostSeeder::class);

        foreach (self::BUCKET_B_SLUGS as $slug) {
            $post = BlogPost::where('slug', $slug)->firstOrFail();

            $this->assertStringContainsString(
                'internal engineering checklist',
                $post->body,
                "Expected {$slug} body to clarify the percentage is an internal engineering checklist metric."
            );
            $this->assertStringContainsString(
                'not a government compliance certification',
                $post->body,
                "Expected {$slug} body to clarify the percentage is not a government compliance certification."
            );
        }
    }
}
