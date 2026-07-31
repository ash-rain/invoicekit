<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\BlogCategories\Pages\CreateBlogCategory;
use App\Filament\Resources\BlogCategories\Pages\EditBlogCategory;
use App\Filament\Resources\BlogCategories\Pages\ListBlogCategories;
use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_categories_list_requires_admin_auth(): void
    {
        $this->get('/admin/blog-categories')->assertRedirect('/admin/login');
    }

    public function test_web_user_cannot_access_blog_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/admin/blog-categories')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_blog_categories_list(): void
    {
        $admin = Admin::factory()->create();
        BlogCategory::factory()->count(3)->create();

        $this->actingAs($admin, 'admin')->get('/admin/blog-categories')->assertOk();
    }

    public function test_blog_categories_table_shows_posts_count(): void
    {
        $admin = Admin::factory()->create();
        $category = BlogCategory::factory()->create();
        BlogPost::factory()->count(2)->published()->for($category, 'category')->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(ListBlogCategories::class)
            ->assertTableColumnExists('posts_count')
            ->assertTableColumnStateSet('posts_count', 2, record: $category);
    }

    public function test_admin_can_create_blog_category(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        Livewire::test(CreateBlogCategory::class)
            ->fillForm(['name' => 'News', 'slug' => 'news'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_categories', ['name' => 'News', 'slug' => 'news']);
    }

    public function test_slug_autofills_from_name_on_create(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        Livewire::test(CreateBlogCategory::class)
            ->set('data.name', 'Product Updates')
            ->assertSet('data.slug', 'product-updates');
    }

    public function test_admin_can_edit_blog_category(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Old Name']);
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        Livewire::test(EditBlogCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_deleting_category_nulls_out_post_category_id(): void
    {
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->published()->for($category, 'category')->create();

        $category->delete();

        $this->assertNull($post->fresh()->category_id);
    }
}
