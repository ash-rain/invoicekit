<div class="p-6">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ __('Projects') }}
            </a>
            <div class="flex items-center gap-3">
                <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">{{ $project->name }}</h1>
                @if ($project->status === 'archived')
                    <span class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 rounded-full">{{ __('Archived') }}</span>
                @endif
            </div>
            @if ($project->client)
                <p class="text-sm text-gray-500 mt-0.5">{{ $project->client->name }}</p>
            @endif
        </div>
        <a href="{{ route('projects.edit', $project) }}"
            class="px-4 py-2.5 text-sm font-semibold border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
            {{ __('Edit Project') }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(99,102,241,0.1);">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Hours') }}</p>
                <p class="text-2xl font-bold text-[#0f1117] mt-0.5">{{ number_format($totalHours, 1) }}h</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(245,158,11,0.1);">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Hourly Rate') }}</p>
                <p class="text-2xl font-bold text-[#0f1117] mt-0.5">
                    @if ($project->hourly_rate)
                        {{ number_format($project->hourly_rate, 2) }} {{ $project->currency }}
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(16,185,129,0.1);">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Earnings') }}</p>
                <p class="text-2xl font-bold text-[#0f1117] mt-0.5">
                    @if ($totalEarnings !== null)
                        {{ number_format($totalEarnings, 2) }} {{ $project->currency }}
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Time Entries --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900">{{ __('Time Entries') }}</h2>
        <a href="{{ route('timer') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('Go to Timer') }}
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-[#eaecf0] overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-[#fafafa]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Description') }}</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Duration') }}</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-t border-[#f3f4f6]" wire:key="entry-{{ $entry->id }}"
                        style="transition:background 0.1s" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $entry->started_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $entry->description ?: '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">
                            @if ($entry->duration_minutes)
                                @php
                                    $h = intdiv($entry->duration_minutes, 60);
                                    $m = $entry->duration_minutes % 60;
                                @endphp
                                {{ $h > 0 ? "{$h}h " : '' }}{{ $m > 0 ? "{$m}m" : ($h === 0 ? '0m' : '') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button wire:click="deleteEntry({{ $entry->id }})"
                                wire:confirm="{{ __('Delete this time entry?') }}"
                                class="text-red-500 hover:text-red-700 text-xs font-semibold">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400">
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
