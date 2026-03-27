<div x-data="{ open: false }" @click.outside="open = false" class="relative" wire:poll.15s>
    {{-- Bell button --}}
    <button @click="open = !open"
        class="relative w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition-all"
        style="color:rgba(255,255,255,0.4);"
        onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.8)'"
        onmouseout="this.style.background='';this.style.color='rgba(255,255,255,0.4)'"
        aria-label="{{ __('Notifications') }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.437L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="flex-1 text-left text-xs font-medium truncate">{{ __('Notifications') }}</span>
        @if ($this->unreadCount > 0)
            <span
                class="shrink-0 flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold"
                style="background:#ef4444;color:#fff;">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute bottom-full left-0 right-0 mb-1 rounded-xl overflow-hidden z-50"
        style="background:#1a1d28;border:1px solid rgba(255,255,255,0.1);box-shadow:0 -24px 64px rgba(0,0,0,0.7);max-height:420px;">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3"
            style="border-bottom:1px solid rgba(255,255,255,0.07);">
            <span class="text-xs font-semibold" style="color:rgba(255,255,255,0.7);">{{ __('Notifications') }}</span>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-[10px] font-medium transition-colors"
                    style="color:#f59e0b;" onmouseover="this.style.color='#fbbf24'"
                    onmouseout="this.style.color='#f59e0b'">
                    {{ __('Mark all read') }}
                </button>
            @endif
        </div>

        {{-- Push setup prompt (shown when browser push is not yet granted) --}}
        <div x-data="{ pushGranted: ('Notification' in window && Notification.permission === 'granted') }" x-show="!pushGranted" class="flex items-center justify-between gap-3 px-4 py-2.5"
            style="background:rgba(245,158,11,0.08);border-bottom:1px solid rgba(245,158,11,0.12);">
            <span class="text-[11px]" style="color:rgba(255,255,255,0.5);">🔔 {{ __('Push notifications off') }}</span>
            <button class="text-[11px] font-semibold px-2.5 py-1 rounded-lg transition-all"
                style="background:rgba(245,158,11,0.2);color:#f59e0b;"
                onmouseover="this.style.background='rgba(245,158,11,0.35)'"
                onmouseout="this.style.background='rgba(245,158,11,0.2)'"
                @click="window.ikRequestPushPermission?.().then(() => { pushGranted = ('Notification' in window && Notification.permission === 'granted') })">
                {{ __('Enable') }}
            </button>
        </div>

        {{-- Notification list --}}
        <div class="overflow-y-auto" style="max-height:340px;">
            @forelse ($this->notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $icon = match ($data['type'] ?? '') {
                        'invoice_paid' => '💰',
                        'invoice_reminder' => '🔔',
                        default => '📌',
                    };
                @endphp
                <div class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-all"
                    style="{{ $isUnread ? 'background:rgba(245,158,11,0.06);' : '' }}"
                    onmouseover="this.style.background='rgba(255,255,255,0.04)'"
                    onmouseout="this.style.background='{{ $isUnread ? 'rgba(245,158,11,0.06)' : '' }}'"
                    wire:click="markAsRead('{{ $notification->id }}')">
                    <span class="text-base shrink-0 mt-0.5">{{ $icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs leading-relaxed"
                            style="color:rgba(255,255,255,{{ $isUnread ? '0.85' : '0.45' }});">
                            {{ $data['message'] ?? '' }}
                        </p>
                        <p class="text-[10px] mt-1" style="color:rgba(255,255,255,0.2);">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @if ($isUnread)
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 mt-1.5" style="background:#f59e0b;"></span>
                    @endif
                </div>
                @if (!$loop->last)
                    <div style="border-bottom:1px solid rgba(255,255,255,0.04);"></div>
                @endif
            @empty
                <div class="flex flex-col items-center justify-center py-10 px-4">
                    <svg class="w-8 h-8 mb-3" style="color:rgba(255,255,255,0.1);" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.437L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-xs text-center" style="color:rgba(255,255,255,0.2);">
                        {{ __('No notifications yet') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
