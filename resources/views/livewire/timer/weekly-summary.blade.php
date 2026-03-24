<div class="bg-white rounded-xl shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Weekly Summary</h2>
        <div class="flex items-center gap-2">
            <button wire:click="previousWeek" class="p-1 rounded hover:bg-gray-100 text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="text-sm text-gray-600 font-medium">
                {{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}
            </span>
            <button wire:click="nextWeek" class="p-1 rounded hover:bg-gray-100 text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    @if($byProject->isEmpty())
        <p class="text-sm text-gray-500 text-center py-6">No time tracked this week.</p>
    @else
        <div class="space-y-3">
            @foreach($byProject as $item)
                @php
                    $hours   = number_format($item['minutes'] / 60, 1);
                    $percent = $totalMinutes > 0 ? ($item['minutes'] / $totalMinutes) * 100 : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-700">
                            {{ $item['project']?->client?->name }} / {{ $item['project']?->name ?? 'Unknown' }}
                        </span>
                        <span class="font-mono font-semibold text-indigo-700">{{ $hours }}h</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div
                            class="bg-indigo-500 h-2 rounded-full"
                            style="width: {{ number_format($percent, 1) }}%"
                        ></div>
                    </div>
                </div>
            @endforeach

            <div class="pt-3 border-t border-gray-200 flex items-center justify-between text-sm font-semibold">
                <span class="text-gray-900">Total</span>
                <span class="font-mono text-indigo-700">{{ number_format($totalMinutes / 60, 1) }}h</span>
            </div>
        </div>
    @endif
</div>
