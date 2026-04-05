<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

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
