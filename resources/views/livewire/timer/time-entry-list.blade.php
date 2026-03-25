<div>
    @if($totalsByProject->isNotEmpty())
        <div class="mb-6 bg-white rounded-xl shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">{{ __('Total Hours by Project') }}</h3>
            <div class="space-y-2">
                @foreach($totalsByProject as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">
                            {{ $item['project']?->client?->name }} / {{ $item['project']?->name ?? __('Unknown project') }}
                        </span>
                        <span class="font-mono font-semibold text-indigo-700">
                            {{ number_format($item['minutes'] / 60, 1) }}h
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($entries->isEmpty())
        <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">
            {{ __('No time entries yet. Start the timer or add a manual entry above.') }}
        </div>
    @else
        @foreach($entries as $date => $dayEntries)
            <div class="bg-white rounded-xl shadow mb-4 overflow-hidden">
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </span>
                    <span class="text-sm font-mono text-indigo-700 font-semibold">
                        {{ number_format($dayEntries->sum('duration_minutes') / 60, 1) }}h
                    </span>
                </div>
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dayEntries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ $entry->project?->client?->name }} / {{ $entry->project?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $entry->description ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono whitespace-nowrap">
                                    {{ $entry->started_at->format('H:i') }}–{{ $entry->stopped_at->format('H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm font-mono font-semibold text-indigo-700 whitespace-nowrap">
                                    {{ number_format($entry->duration_minutes / 60, 1) }}h
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        wire:click="deleteEntry({{ $entry->id }})"
                                        wire:confirm="{{ __('Delete this time entry?') }}"
                                        class="text-xs text-red-500 hover:text-red-700"
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
