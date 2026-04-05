<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>InvoiceKit Blog</title>
        <link>{{ url('/blog') }}</link>
        <description>Articles, guides and updates from InvoiceKit.</description>
        <language>en-gb</language>
        <lastBuildDate>{{ now()->toRfc1123String() }}</lastBuildDate>
        <atom:link href="{{ route('blog.feed') }}" rel="self" type="application/rss+xml" />
        <image>
            <url>{{ url('/images/og-thumb.png') }}</url>
            <title>InvoiceKit Blog</title>
            <link>{{ url('/blog') }}</link>
        </image>

        @foreach ($posts as $post)
            @php
                $link = route('blog.show', $post->slug);
                $image = $post->featured_image
                    ? \Illuminate\Support\Facades\Storage::disk('s3')->url($post->featured_image)
                    : url('/images/og-thumb.png');
            @endphp
            <item>
                <title>
                    <![CDATA[{{ $post->title }}]]>
                </title>
                <link>{{ $link }}</link>
                <guid isPermaLink="true">{{ $link }}</guid>
                <pubDate>{{ $post->published_at->toRfc1123String() }}</pubDate>
                @if ($post->admin)
                    <author>{{ $post->admin->email }} ({{ $post->admin->name }})</author>
                @endif
                <description>
                    <![CDATA[{{ $post->excerpt }}]]>
                </description>
                <content:encoded>
                    <![CDATA[
                <img src="{{ $image }}" alt="{{ $post->title }}">
                {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($post->body)->fileAttachmentsDisk('s3')->toHtml() !!}
            ]]>
                </content:encoded>
                @if ($post->meta_description)
                    <description>
                        <![CDATA[{{ $post->meta_description }}]]>
                    </description>
                @endif
            </item>
        @endforeach
    </channel>
</rss>
