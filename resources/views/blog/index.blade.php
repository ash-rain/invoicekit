@extends('layouts.public')

@section('title', 'Blog — InvoiceKit')
@section('meta_description', 'Articles, guides and updates from InvoiceKit.')
@section('og_title', 'InvoiceKit Blog')
@section('og_description', 'Articles, guides and updates from InvoiceKit.')
@section('og_url', route('blog.index'))
@section('canonical', route('blog.index'))
@section('body_class', 'bg-gradient-to-b from-indigo-50 via-white to-white')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Blog</h1>
            <p class="text-lg text-gray-500">Articles, guides and updates from InvoiceKit.</p>
        </div>

        @include('blog.partials.category-nav', ['categories' => $categories, 'activeCategory' => $activeCategory])

        @if ($posts->isEmpty())
            <p class="text-gray-500">
                @if ($activeCategory)
                    No posts in {{ $activeCategory->name }} yet — check back soon.
                @else
                    No posts yet — check back soon.
                @endif
            </p>
        @else
            <div id="blog-posts" class="flex flex-col gap-6">
                @include('blog.partials.posts-list', ['posts' => $posts])
            </div>

            <div id="blog-infinite-sentinel" class="h-16 flex items-center justify-center"
                data-next-page="{{ $posts->currentPage() + 1 }}"
                data-has-more="{{ $posts->hasMorePages() ? 'true' : 'false' }}"
                data-category="{{ $activeCategory?->slug }}">
                <span id="blog-infinite-loading" class="hidden text-sm text-gray-400">Loading more posts…</span>
            </div>

            <script>
                (function () {
                    const container = document.getElementById('blog-posts');
                    const sentinel = document.getElementById('blog-infinite-sentinel');
                    const loadingIndicator = document.getElementById('blog-infinite-loading');

                    if (!container || !sentinel) {
                        return;
                    }

                    let isLoading = false;

                    const observer = new IntersectionObserver((entries) => {
                        const entry = entries[0];

                        if (!entry.isIntersecting || isLoading || sentinel.dataset.hasMore !== 'true') {
                            return;
                        }

                        isLoading = true;
                        loadingIndicator?.classList.remove('hidden');

                        const params = new URLSearchParams({ page: sentinel.dataset.nextPage });
                        if (sentinel.dataset.category) {
                            params.set('category', sentinel.dataset.category);
                        }

                        fetch(`{{ route('blog.index') }}?${params.toString()}`, {
                            headers: { Accept: 'application/json' },
                        })
                            .then((response) => {
                                if (!response.ok) {
                                    throw new Error('Failed to load more posts');
                                }
                                return response.json();
                            })
                            .then((data) => {
                                container.insertAdjacentHTML('beforeend', data.html);
                                sentinel.dataset.nextPage = Number(sentinel.dataset.nextPage) + 1;
                                sentinel.dataset.hasMore = data.hasMore ? 'true' : 'false';

                                if (!data.hasMore) {
                                    observer.disconnect();
                                }
                            })
                            .catch(() => {
                                sentinel.dataset.hasMore = 'false';
                                observer.disconnect();
                            })
                            .finally(() => {
                                isLoading = false;
                                loadingIndicator?.classList.add('hidden');
                            });
                    }, { rootMargin: '200px' });

                    if (sentinel.dataset.hasMore === 'true') {
                        observer.observe(sentinel);
                    }
                })();
            </script>
        @endif
    </div>
@endsection
