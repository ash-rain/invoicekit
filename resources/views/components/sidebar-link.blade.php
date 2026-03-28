@props(['href', 'active' => false, 'icon' => 'circle'])

@php
    $icons = [
        'grid' => 'M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z',
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'users' =>
            'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'folder' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
        'document' =>
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'credit-card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'receipt' =>
            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'circle' => 'M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0-8 0',
    ];
    $path = $icons[$icon] ?? $icons['circle'];
@endphp

@if ($active)
    <a href="{{ $href }}"
        {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold']) }}
        style="background:rgba(245,158,11,0.12);color:#fbbf24;box-shadow:inset 0 0 0 1px rgba(245,158,11,0.18);">
        <svg style="width:17px;height:17px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="{{ $path }}" />
        </svg>
        {{ $slot }}
    </a>
@else
    <a href="{{ $href }}"
        {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all']) }}
        style="color:rgba(255,255,255,0.4);"
        onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.85)'"
        onmouseout="this.style.background='';this.style.color='rgba(255,255,255,0.4)'">
        <svg style="width:17px;height:17px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="{{ $path }}" />
        </svg>
        {{ $slot }}
    </a>
@endif
