<div class="p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">{{ __('Projects') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Track time and manage client projects') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search projects...') }}"
                class="w-full sm:w-52 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <a href="{{ route('projects.create') }}"
                class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#0f1117] text-white rounded-xl text-sm font-bold hover:bg-[#1a1f2e]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('New Project') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-5">
        <button wire:click="$set('tab', 'active')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $tab === 'active' ? 'bg-[#0f1117] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            {{ __('Active') }}
        </button>
        <button wire:click="$set('tab', 'archived')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $tab === 'archived' ? 'bg-[#0f1117] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            {{ __('Archived') }}
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-[#eaecf0] overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-[#fafafa]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Project') }}</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Client') }}</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Rate') }}</th>
                    <th class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Hours Logged') }}</th>
                    <th class="px-6 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr class="border-t border-[#f3f4f6]" wire:key="project-{{ $project->id }}"
                        style="transition:background 0.1s" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('projects.show', $project) }}"
                                class="text-sm font-semibold text-[#0f1117] hover:text-indigo-600">
                                {{ $project->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $project->client->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                            @if ($project->hourly_rate)
                                {{ number_format($project->hourly_rate, 2) }} {{ $project->currency }}/h
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @php $hours = round(($project->total_minutes ?? 0) / 60, 1); @endphp
                            {{ $hours }}h
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                            <a href="{{ route('projects.edit', $project) }}"
                                class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                            @if ($tab === 'active')
                                <button wire:click="archiveProject({{ $project->id }})"
                                    wire:confirm="{{ __('Archive this project?') }}"
                                    class="text-amber-600 hover:text-amber-800">{{ __('Archive') }}</button>
                            @else
                                <button wire:click="restoreProject({{ $project->id }})"
                                    class="text-green-600 hover:text-green-800">{{ __('Restore') }}</button>
                            @endif
                            <button wire:click="deleteProject({{ $project->id }})"
                                wire:confirm="{{ __('Permanently delete this project? This cannot be undone.') }}"
                                class="text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">
                            {{ $tab === 'active' ? __('No active projects. Create one to get started.') : __('No archived projects.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</div>
