<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $this->project && $this->project->exists ? __('Edit Project') : __('New Project') }}
        </h2>
        <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            {{ __('← Back to projects') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form wire:submit="save" class="space-y-5">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Project Name') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="name" type="text" placeholder="{{ __('Website Redesign') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror" />
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Client --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client') }}</label>
                <select wire:model.live="client_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('client_id') border-red-400 @enderror">
                    <option value="">{{ __('— No client —') }}</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Hourly Rate + Currency --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Hourly Rate') }}</label>
                    <input wire:model="hourly_rate" type="number" step="0.01" min="0"
                        placeholder="{{ __('0.00') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('hourly_rate') border-red-400 @enderror" />
                    @error('hourly_rate')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Currency') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="currency"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('currency') border-red-400 @enderror">
                        @foreach ($currencies as $cur)
                            <option value="{{ $cur }}">{{ $cur }}</option>
                        @endforeach
                    </select>
                    @error('currency')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Status (edit only) --}}
            @if ($this->project && $this->project->exists)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                    <select wire:model="status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active">{{ __('Active') }}</option>
                        <option value="archived">{{ __('Archived') }}</option>
                    </select>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('projects.index') }}"
                    class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ $this->project && $this->project->exists ? __('Update Project') : __('Create Project') }}
                </button>
            </div>

        </form>
    </div>
</div>
