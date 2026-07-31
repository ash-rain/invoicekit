<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $categories = BlogCategory::orderBy('name')->get();
        $activeCategory = $categories->firstWhere('slug', $request->query('category'));

        $posts = BlogPost::published()
            ->with('admin')
            ->when($activeCategory, fn ($query) => $query->where('category_id', $activeCategory->id))
            ->latest('published_at')
            ->paginate(6);

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('blog.partials.posts-list', compact('posts'))->render(),
                'hasMore' => $posts->hasMorePages(),
            ]);
        }

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(string $slug): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        $post = BlogPost::published()
            ->where('slug', $slug)
            ->with(['admin', 'category'])
            ->firstOrFail();

        return view('blog.show', compact('post', 'categories'));
    }

    public function feed(): Response
    {
        $posts = BlogPost::published()
            ->with('admin')
            ->latest('published_at')
            ->limit(20)
            ->get();

        $xml = view('blog.feed', compact('posts'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
