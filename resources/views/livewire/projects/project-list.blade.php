<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Projects') }}</h2>
        <a href="{{ route('projects.create') }}"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
            {{ __('+ New Project') }}
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-4 border-b border-gray-200">
        <button wire:click="$set('tab', 'active')"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === 'active' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ __('Active') }}
        </button>
        <button wire:click="$set('tab', 'archived')"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === 'archived' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ __('Archived') }}
        </button>
    </div>

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search projects...') }}"
            class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Project') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Client') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Rate') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Hours Logged') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50" wire:key="project-{{ $project->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('projects.show', $project) }}"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ $project->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $project->client->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if ($project->hourly_rate)
                                {{ number_format($project->hourly_rate, 2) }} {{ $project->currency }}/h
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @php $hours = round(($project->total_minutes ?? 0) / 60, 1); @endphp
                            {{ $hours }}h
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('projects.edit', $project) }}"
                                class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                            @if ($tab === 'active')
                                <button wire:click="archiveProject({{ $project->id }})"
                                    wire:confirm="{{ __('Archive this project?') }}"
                                    class="text-yellow-600 hover:text-yellow-900">{{ __('Archive') }}</button>
                            @else
                                <button wire:click="restoreProject({{ $project->id }})"
                                    class="text-green-600 hover:text-green-900">{{ __('Restore') }}</button>
                            @endif
                            <button wire:click="deleteProject({{ $project->id }})"
                                wire:confirm="{{ __('Permanently delete this project? This cannot be undone.') }}"
                                class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
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
