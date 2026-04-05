@extends('layouts.public')

@section('title', 'Blog — InvoiceKit')
@section('meta_description', 'Articles, guides and updates from InvoiceKit.')
@section('og_title', 'InvoiceKit Blog')
@section('og_description', 'Articles, guides and updates from InvoiceKit.')
@section('og_url', route('blog.index'))
@section('canonical', route('blog.index'))

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Blog</h1>
            <p class="text-lg text-gray-500">Articles, guides and updates from InvoiceKit.</p>
        </div>

        @if ($posts->isEmpty())
            <p class="text-gray-500">No posts yet — check back soon.</p>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article
                        class="group flex flex-col rounded-2xl border border-gray-100 overflow-hidden hover:border-indigo-200 hover:shadow-md transition-all duration-200">
                        @if ($post->featured_image)
                            <div class="aspect-video overflow-hidden bg-gray-50">
                                <img src="{{ Storage::disk('s3')->url($post->featured_image) }}" alt="{{ $post->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div
                                class="aspect-video overflow-hidden bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
                                <img src="{{ url('/images/og-thumb.png') }}" alt="InvoiceKit"
                                    class="h-12 w-12 object-contain opacity-40">
                            </div>
                        @endif

                        <div class="flex flex-col flex-1 p-5">
                            <div class="text-xs text-gray-400 mb-2">
                                {{ $post->published_at->format('M j, Y') }}
                                @if ($post->admin)
                                    · {{ $post->admin->name }}
                                @endif
                                · {{ $post->reading_time }} min read
                            </div>
                            <h2
                                class="text-base font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            <p class="text-sm text-gray-500 line-clamp-3 flex-1">{{ $post->excerpt }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}"
                                class="mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                Read more →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
