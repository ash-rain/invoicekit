<article
    class="group flex flex-col sm:flex-row gap-6 rounded-2xl border border-gray-100 bg-white overflow-hidden hover:border-indigo-200 hover:shadow-md transition-all duration-200">
    <a href="{{ route('blog.show', $post->slug) }}" aria-label="{{ $post->title }}"
        class="block sm:w-72 flex-shrink-0 aspect-video sm:aspect-square overflow-hidden">
        @if ($post->featured_image)
            <img src="{{ Storage::disk('s3')->url($post->featured_image) }}" alt="{{ $post->title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="relative w-full h-full grid place-items-center bg-gradient-to-br from-indigo-50 to-purple-50">
                <div class="col-start-1 row-start-1 w-2/3 aspect-square rounded-full bg-indigo-300/40 blur-2xl"></div>
                <span class="col-start-1 row-start-1 relative z-10 text-6xl sm:text-7xl leading-none">{{ $post->thumbnail }}</span>
            </div>
        @endif
    </a>

    <div class="flex flex-col flex-1 p-6">
        <div class="text-xs text-gray-400 mb-2">
            {{ $post->published_at->format('M j, Y') }}
            @if ($post->admin)
                · {{ $post->admin->name }}
            @endif
            · {{ $post->reading_time }} min read
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-3 group-hover:text-indigo-600 transition-colors">
            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
        </h2>
        <p class="text-sm leading-relaxed text-gray-500 whitespace-pre-line flex-1">{{ $post->preview }}</p>
        <a href="{{ route('blog.show', $post->slug) }}"
            class="mt-4 inline-flex w-fit text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
            Read more →
        </a>
    </div>
</article>
