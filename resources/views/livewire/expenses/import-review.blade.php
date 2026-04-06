<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight"
                style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ __('Extracted Data') }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">
                {{ __('Review and confirm to create your :type.', ['type' => __('expense')]) }}
                — @if($this->fileUrl)<a href="{{ $this->fileUrl }}" target="_blank" class="font-medium text-indigo-600 hover:underline">{{ $import->original_filename }}</a>@else<span class="font-medium text-gray-700">{{ $import->original_filename }}</span>@endif
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <button wire:click="deleteImport" wire:confirm="{{ __('Delete this import and its file?') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-red-200 text-red-500 hover:bg-red-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                {{ __('Delete') }}
            </button>
            <button wire:click="skip"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                {{ __('Skip') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form --}}
        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">{{ __('Expense Details') }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Description') }} <span
                                class="text-red-500">*</span></label>
                        <input wire:model="description" type="text"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                        @error('description')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Amount') }} <span
                                class="text-red-500">*</span></label>
                        <input wire:model="amount" type="number" min="0.01" step="0.01"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                        @error('amount')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Currency') }}</label>
                        <input wire:model="currency" type="text" maxlength="10"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Date') }} <span
                                class="text-red-500">*</span></label>
                        <input wire:model="date" type="date"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                        @error('date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Category') }}</label>
                        <select wire:model="category"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                            <option value="software">{{ __('Software') }}</option>
                            <option value="hardware">{{ __('Hardware') }}</option>
                            <option value="travel">{{ __('Travel') }}</option>
                            <option value="hosting">{{ __('Hosting') }}</option>
                            <option value="marketing">{{ __('Marketing') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Client') }}</label>
                        <select wire:model="clientId"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                            <option value="">{{ __('All Clients') }}</option>
                            @foreach ($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Project') }}</label>
                        <select wire:model="projectId"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                            <option value="">— {{ __('No project') }}</option>
                            @foreach ($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-3">
                        <input wire:model="billable" type="checkbox" id="billable-check"
                            class="w-4 h-4 rounded text-indigo-600">
                        <label for="billable-check" class="text-sm text-gray-700">{{ __('Mark as billable') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Amount') }}</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $currency }}
                    {{ number_format((float) $amount, 2) }}</p>

                <button wire:click="confirm" wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all"
                    style="background:#0f1117;color:white;" onmouseover="this.style.background='#1e2130'"
                    onmouseout="this.style.background='#0f1117'">
                    <span wire:loading.remove>{{ __('Confirm Import') }}</span>
                    <span wire:loading>{{ __('Saving...') }}</span>
                </button>
            </div>

            {{-- Source document + receipt note --}}
            <div class="bg-white rounded-2xl p-5" style="border:1px solid #eaecf0;">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">{{ __('Source Document') }}
                </h2>
                <div class="flex items-center gap-3 p-3 rounded-xl"
                    style="background:#fafafa;border:1px solid #f3f4f6;">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    @if($this->fileUrl)
                        <a href="{{ $this->fileUrl }}" target="_blank"
                            class="text-xs font-medium text-indigo-600 hover:underline truncate">{{ $import->original_filename }}</a>
                    @else
                        <p class="text-xs font-medium text-gray-700 truncate">{{ $import->original_filename }}</p>
                    @endif
            </div>
        </div>
    </div>
</div>
