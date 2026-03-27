<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                {{ $this->project && $this->project->exists ? __('Edit Project') : __('New Project') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $this->project && $this->project->exists ? __('Update project details') : __('Create a new project') }}</p>
        </div>
        <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to Projects') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
        <form wire:submit="save" class="space-y-5">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Project Name') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="name" type="text" placeholder="{{ __('Website Redesign') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror" />
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Client --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client') }}</label>
                <select wire:model.live="client_id"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('client_id') border-red-400 @enderror">
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
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('hourly_rate') border-red-400 @enderror" />
                    @error('hourly_rate')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Currency') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="currency"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('currency') border-red-400 @enderror">
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
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active">{{ __('Active') }}</option>
                        <option value="archived">{{ __('Archived') }}</option>
                    </select>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('projects.index') }}"
                    class="px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                    {{ $this->project && $this->project->exists ? __('Update Project') : __('Create Project') }}
                </button>
            </div>

        </form>
    </div>
</div>
