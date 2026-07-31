@php
    $activeSlug = $activeCategory?->slug;
@endphp
<nav class="flex flex-wrap gap-2 mb-10" aria-label="Blog categories">
    <a href="{{ route('blog.index') }}"
        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors {{ $activeSlug === null ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        All
    </a>
    @foreach ($categories as $category)
        <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors {{ $activeSlug === $category->slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $category->name }}
        </a>
    @endforeach
</nav>
