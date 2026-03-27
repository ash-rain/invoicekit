<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    {{ __('← Projects') }}
                </a>
                @if($project->status === 'archived')
                    <span class="px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700 rounded-full">{{ __('Archived') }}</span>
                @endif
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ $project->name }}</h2>
            @if($project->client)
                <p class="text-sm text-gray-500 mt-0.5">{{ $project->client->name }}</p>
            @endif
        </div>
        <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            {{ __('Edit Project') }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">{{ __('Total Hours') }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalHours, 1) }}h</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">{{ __('Hourly Rate') }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
                @if($project->hourly_rate)
                    {{ number_format($project->hourly_rate, 2) }} {{ $project->currency }}
                @else
                    —
                @endif
            </p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">{{ __('Total Earnings') }}</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">
                @if($totalEarnings !== null)
                    {{ number_format($totalEarnings, 2) }} {{ $project->currency }}
                @else
                    —
                @endif
            </p>
        </div>
    </div>

    {{-- Time Entries --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-gray-900">{{ __('Time Entries') }}</h3>
        <a
            href="{{ route('timer') }}"
            class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700"
        >
            {{ __('→ Go to Timer') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($entries as $entry)
                    <tr class="hover:bg-gray-50" wire:key="entry-{{ $entry->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $entry->started_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $entry->description ?: '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($entry->duration_minutes)
                                @php
                                    $h = intdiv($entry->duration_minutes, 60);
                                    $m = $entry->duration_minutes % 60;
                                @endphp
                                {{ $h > 0 ? "{$h}h " : '' }}{{ $m > 0 ? "{$m}m" : ($h === 0 ? '0m' : '') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                wire:click="deleteEntry({{ $entry->id }})"
                                wire:confirm="{{ __('Delete this time entry?') }}"
                                class="text-red-600 hover:text-red-900"
                            >{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                            {{ __('No time entries yet. Start tracking time in the Timer.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>

</div>
