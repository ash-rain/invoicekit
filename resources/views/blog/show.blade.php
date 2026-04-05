@extends('layouts.public')

@php
    $ogImage = $post->featured_image
        ? \Illuminate\Support\Facades\Storage::disk('s3')->url($post->featured_image)
        : url('/images/og-thumb.png');
    $ogTitle = $post->meta_title ?: $post->title;
    $ogDescription = $post->meta_description ?: $post->excerpt;
    $supportedLangs = config('invoicekit.supported_languages', []);
@endphp

@section('title', $ogTitle . ' — InvoiceKit Blog')
@section('meta_description', $ogDescription)
@section('og_type', 'article')
@section('og_url', route('blog.show', $post->slug))
@section('canonical', route('blog.show', $post->slug))
@section('og_title', $ogTitle)
@section('og_description', $ogDescription)
@section('og_image', $ogImage)

@section('structured_data')
    <script type="application/ld+json">
@php
echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post->title,
    'description' => $ogDescription,
    'image' => $ogImage,
    'datePublished' => $post->published_at->toIso8601String(),
    'dateModified' => $post->updated_at->toIso8601String(),
    'author' => [
        '@type' => 'Person',
        'name' => $post->admin?->name ?? 'InvoiceKit',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'InvoiceKit',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => url('/img/logo.png'),
        ],
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('blog.show', $post->slug),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@endphp
</script>
@endsection

@section('content')
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-8" aria-label="Breadcrumb">
            <a href="/" class="hover:text-gray-600">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-gray-600">Blog</a>
            <span class="mx-2">/</span>
            <span class="text-gray-600">{{ $post->title }}</span>
        </nav>

        {{-- Header --}}
        <header class="mb-10">
            <h1 class="text-4xl font-bold text-gray-900 leading-tight mb-4">{{ $post->title }}</h1>
            <div class="flex items-center gap-3 text-sm text-gray-400">
                <time datetime="{{ $post->published_at->toDateString() }}">
                    {{ $post->published_at->format('F j, Y') }}
                </time>
                @if ($post->admin)
                    <span>·</span>
                    <span>{{ $post->admin->name }}</span>
                @endif
                <span>·</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </header>

        {{-- Featured Image --}}
        @if ($post->featured_image)
            <div class="rounded-2xl overflow-hidden mb-10 aspect-video bg-gray-50">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($post->featured_image) }}"
                    alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Body --}}
        <div class="prose prose-gray prose-headings:font-bold prose-a:text-indigo-600 max-w-none">
            {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($post->body)->fileAttachmentsDisk('s3')->toHtml() !!}
        </div>

        {{-- Footer / Back --}}
        <div class="mt-16 pt-8 border-t border-gray-100">
            <a href="{{ route('blog.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                ← Back to Blog
            </a>
        </div>
    </article>
@endsection
