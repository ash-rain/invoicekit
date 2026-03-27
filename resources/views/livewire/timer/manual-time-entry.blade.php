<div class="bg-white rounded-2xl border border-[#eaecf0] overflow-hidden">
    <div class="px-6 py-4 border-b border-[#eaecf0]">
        <h2 class="text-sm font-bold text-gray-900">{{ __('Add Manual Entry') }}</h2>
    </div>

    <div class="p-6">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Project') }}</label>
                    <select
                        wire:model="projectId"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('— Select project —') }}</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->client->name }} / {{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('projectId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }}</label>
                    <input
                        wire:model="date"
                        type="date"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start Time') }}</label>
                    <input
                        wire:model="startTime"
                        type="time"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    @error('startTime') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End Time') }}</label>
                    <input
                        wire:model="endTime"
                        type="time"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    @error('endTime') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                <input
                    wire:model="description"
                    type="text"
                    placeholder="{{ __('What did you work on?') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] text-sm font-bold"
                >
                    {{ __('Save Entry') }}
                </button>
            </div>
        </form>
    </div>
</div>
