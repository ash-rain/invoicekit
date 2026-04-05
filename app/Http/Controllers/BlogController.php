<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $posts = BlogPost::published()
            ->with('admin')
            ->latest('published_at')
            ->paginate(12);

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug): \Illuminate\View\View
    {
        $post = BlogPost::published()
            ->where('slug', $slug)
            ->with('admin')
            ->firstOrFail();

        return view('blog.show', compact('post'));
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
