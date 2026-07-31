<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_shows_category_nav_with_all_categories(): void
    {
        BlogCategory::factory()->create(['name' => 'Compliance']);
        BlogCategory::factory()->create(['name' => 'News']);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('All');
        $response->assertSee('Compliance');
        $response->assertSee('News');
    }

    public function test_blog_index_filters_posts_by_category(): void
    {
        $compliance = BlogCategory::factory()->create();
        $news = BlogCategory::factory()->create();
        $compliancePost = BlogPost::factory()->published()->for($compliance, 'category')->create();
        $newsPost = BlogPost::factory()->published()->for($news, 'category')->create();

        $response = $this->get(route('blog.index', ['category' => $compliance->slug]));

        $response->assertOk();
        $response->assertSee($compliancePost->title);
        $response->assertDontSee($newsPost->title);
    }

    public function test_blog_index_shows_empty_state_for_category_with_no_posts(): void
    {
        $news = BlogCategory::factory()->create(['name' => 'News']);
        BlogPost::factory()->published()->create();

        $response = $this->get(route('blog.index', ['category' => $news->slug]));

        $response->assertOk();
        $response->assertSee('No posts in News yet');
    }

    public function test_blog_index_unknown_category_slug_falls_back_to_all_posts(): void
    {
        $post = BlogPost::factory()->published()->create();

        $response = $this->get(route('blog.index', ['category' => 'does-not-exist']));

        $response->assertOk();
        $response->assertSee($post->title);
    }

    public function test_blog_index_ajax_pagination_respects_active_category(): void
    {
        $compliance = BlogCategory::factory()->create();
        $news = BlogCategory::factory()->create();

        BlogPost::factory()->published()->count(8)->for($compliance, 'category')->sequence(fn ($sequence) => [
            'title' => "Compliance Post {$sequence->index}",
            'published_at' => now()->subMinutes($sequence->index),
        ])->create();
        BlogPost::factory()->published()->count(8)->for($news, 'category')->sequence(fn ($sequence) => [
            'title' => "News Post {$sequence->index}",
        ])->create();

        $response = $this->getJson(route('blog.index', ['category' => $compliance->slug, 'page' => 2]));

        $response->assertOk();
        $this->assertFalse($response->json('hasMore'));
        $html = $response->json('html');
        $this->assertStringContainsString('Compliance Post', $html);
        $this->assertStringNotContainsString('News Post', $html);
    }

    public function test_blog_index_thumbnail_links_to_post_show_page(): void
    {
        $post = BlogPost::factory()->published()->create();

        $response = $this->get(route('blog.index'));

        $response->assertOk();

        $link = 'href="'.route('blog.show', $post->slug).'"';
        $occurrences = substr_count($response->getContent(), $link);

        // Thumbnail, title, and "Read more" should each link to the post.
        $this->assertSame(3, $occurrences);
    }

    public function test_blog_index_shows_published_posts(): void
    {
        $published = BlogPost::factory()->published()->create();
        $draft = BlogPost::factory()->draft()->create();

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
    }

    public function test_blog_index_returns_200(): void
    {
        $response = $this->get(route('blog.index'));

        $response->assertOk();
    }

    public function test_blog_index_shows_flag_emoji_thumbnail(): void
    {
        BlogPost::factory()->published()->create(['thumbnail_emoji' => '🇦🇹']);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('🇦🇹', false);
    }

    public function test_blog_index_shows_fallback_emoji_when_thumbnail_not_set(): void
    {
        BlogPost::factory()->published()->create(['thumbnail_emoji' => null]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('📝', false);
    }

    public function test_blog_index_shows_longer_preview_beyond_old_excerpt_limit(): void
    {
        $filler = str_repeat('lorem ipsum dolor sit amet ', 8);
        $body = '<p>'.$filler.'</p><p>Second paragraph mentions a distinctive phrase past the old excerpt cutoff: zzTruncationCanary.</p>';

        BlogPost::factory()->published()->create(['body' => $body]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('zzTruncationCanary');
    }

    public function test_blog_index_does_not_render_pagination_links(): void
    {
        BlogPost::factory()->published()->count(8)->create();

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertDontSee('rel="next"', false);
        $response->assertDontSee('aria-label="Pagination Navigation"', false);
    }

    public function test_blog_index_ajax_request_returns_json_with_html_and_has_more(): void
    {
        BlogPost::factory()->published()->count(8)->create();

        $response = $this->getJson(route('blog.index'));

        $response->assertOk();
        $response->assertJsonStructure(['html', 'hasMore']);
        $this->assertTrue($response->json('hasMore'));
    }

    public function test_blog_index_ajax_second_page_reports_no_more_pages(): void
    {
        BlogPost::factory()->published()->count(8)->sequence(fn ($sequence) => [
            'title' => "Post {$sequence->index}",
            'published_at' => now()->subMinutes($sequence->index),
        ])->create();

        $response = $this->getJson(route('blog.index', ['page' => 2]));

        $response->assertOk();
        $this->assertFalse($response->json('hasMore'));
        $this->assertNotEmpty($response->json('html'));
    }

    public function test_blog_show_renders_category_nav_with_posts_category_highlighted(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Compliance']);
        $post = BlogPost::factory()->published()->for($category, 'category')->create();

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('Compliance');
    }

    public function test_blog_show_renders_category_nav_when_post_has_no_category(): void
    {
        $post = BlogPost::factory()->published()->create(['category_id' => null]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('All');
    }

    public function test_blog_show_returns_200_for_published_post(): void
    {
        $post = BlogPost::factory()->published()->create();

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee($post->title);
    }

    public function test_blog_show_returns_404_for_draft(): void
    {
        $post = BlogPost::factory()->draft()->create();

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertNotFound();
    }

    public function test_blog_show_returns_404_for_future_post(): void
    {
        $post = BlogPost::factory()->create([
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertNotFound();
    }

    public function test_blog_show_contains_og_meta_tags(): void
    {
        $post = BlogPost::factory()->published()->create();

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:image', false);
        $response->assertSee('twitter:card', false);
    }

    public function test_blog_show_uses_og_thumb_fallback_when_no_featured_image(): void
    {
        $post = BlogPost::factory()->published()->create(['featured_image' => null]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('/images/og-thumb.png', false);
    }

    public function test_blog_show_contains_json_ld_structured_data(): void
    {
        $post = BlogPost::factory()->published()->create();

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('BlogPosting', false);
    }

    public function test_rss_feed_returns_valid_xml(): void
    {
        BlogPost::factory()->published()->count(3)->create();
        BlogPost::factory()->draft()->create();

        $response = $this->get(route('blog.feed'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('<rss', false);
        $response->assertSee('<channel>', false);
    }

    public function test_rss_feed_only_includes_published_posts(): void
    {
        $published = BlogPost::factory()->published()->create();
        $draft = BlogPost::factory()->draft()->create();

        $response = $this->get(route('blog.feed'));

        $response->assertOk();
        $response->assertSee($published->title, false);
        $response->assertDontSee($draft->title, false);
    }

    public function test_sitemap_includes_blog_index(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertSee(url('/blog'), false);
    }

    public function test_sitemap_includes_published_blog_posts(): void
    {
        $post = BlogPost::factory()->published()->create();

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertSee(route('blog.show', $post->slug), false);
    }

    public function test_sitemap_does_not_include_draft_posts(): void
    {
        $draft = BlogPost::factory()->draft()->create();

        $response = $this->get(route('sitemap'));

        $response->assertDontSee(route('blog.show', $draft->slug), false);
    }
}
