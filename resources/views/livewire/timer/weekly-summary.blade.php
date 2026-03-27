<div class="bg-white rounded-2xl border border-[#eaecf0] overflow-hidden">
    <div class="px-6 py-4 border-b border-[#eaecf0] flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-900">{{ __('Weekly Summary') }}</h2>
        <div class="flex items-center gap-1">
            <button wire:click="previousWeek" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="text-xs text-gray-600 font-semibold px-1">
                {{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}
            </span>
            <button wire:click="nextWeek" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    <div class="p-6">
        @if($byProject->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">{{ __('No time tracked this week.') }}</p>
        @else
            <div class="space-y-4">
                @foreach($byProject as $item)
                    @php
                        $hours   = number_format($item['minutes'] / 60, 1);
                        $percent = $totalMinutes > 0 ? ($item['minutes'] / $totalMinutes) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="text-gray-700 text-xs">
                                {{ $item['project']?->client?->name }} / {{ $item['project']?->name ?? __('Unknown') }}
                            </span>
                            <span class="font-mono font-bold text-[#0f1117] text-xs">{{ $hours }}h</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div
                                class="h-1.5 rounded-full"
                                style="width: {{ number_format($percent, 1) }}%; background:#f59e0b;"
                            ></div>
                        </div>
                    </div>
                @endforeach

                <div class="pt-4 border-t border-[#eaecf0] flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-900">{{ __('Total') }}</span>
                    <span class="font-mono font-bold text-[#0f1117]">{{ number_format($totalMinutes / 60, 1) }}h</span>
                </div>
            </div>
        @endif
    </div>
</div>
