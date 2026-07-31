@foreach ($posts as $post)
    @include('blog.partials.post-card', ['post' => $post])
@endforeach
