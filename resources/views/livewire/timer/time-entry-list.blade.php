<div>
    @if($totalsByProject->isNotEmpty())
        <div class="mb-6 bg-white rounded-2xl border border-[#eaecf0] p-6">
            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-4">{{ __('Total Hours by Project') }}</h3>
            <div class="space-y-2">
                @foreach($totalsByProject as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">
                            {{ $item['project']?->client?->name }} / {{ $item['project']?->name ?? __('Unknown project') }}
                        </span>
                        <span class="font-mono font-bold text-[#0f1117]">
                            {{ number_format($item['minutes'] / 60, 1) }}h
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($entries->isEmpty())
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-10 text-center text-gray-400 text-sm">
            {{ __('No time entries yet. Start the timer or add a manual entry above.') }}
        </div>
    @else
        @foreach($entries as $date => $dayEntries)
            <div class="bg-white rounded-2xl border border-[#eaecf0] mb-4 overflow-hidden">
                <div class="px-6 py-3.5 bg-[#fafafa] border-b border-[#eaecf0] flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-700">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </span>
                    <span class="text-sm font-mono font-bold text-[#0f1117]">
                        {{ number_format($dayEntries->sum('duration_minutes') / 60, 1) }}h
                    </span>
                </div>
                <table class="min-w-full">
                    <tbody>
                        @foreach($dayEntries as $entry)
                            <tr class="border-t border-[#f3f4f6]"
                                style="transition:background 0.1s" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                                <td class="px-6 py-3.5 text-sm font-medium text-gray-800">
                                    {{ $entry->project?->client?->name }} / {{ $entry->project?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-sm text-gray-500">
                                    {{ $entry->description ?: '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-sm text-gray-400 font-mono whitespace-nowrap">
                                    {{ $entry->started_at->format('H:i') }}–{{ $entry->stopped_at->format('H:i') }}
                                </td>
                                <td class="px-4 py-3.5 text-sm font-mono font-bold text-[#0f1117] whitespace-nowrap">
                                    {{ number_format($entry->duration_minutes / 60, 1) }}h
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <button
                                        wire:click="deleteEntry({{ $entry->id }})"
                                        wire:confirm="{{ __('Delete this time entry?') }}"
                                        class="text-xs text-red-500 hover:text-red-700 font-semibold"
                                    >{{ __('Delete') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
