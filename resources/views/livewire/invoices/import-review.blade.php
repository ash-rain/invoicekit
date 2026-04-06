<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight"
                style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ __('Extracted Data') }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">
                {{ __('Review and confirm to create your :type.', ['type' => __('invoice')]) }}
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

            {{-- Client + Meta --}}
            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">{{ __('Invoice Details') }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Client') }} <span
                                class="text-red-500">*</span></label>
                        <select wire:model="clientId"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                            <option value="">{{ __('Select a client') }}</option>
                            @foreach ($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('clientId')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        @if (!$clientId)
                            @php
                                $createClientUrl = route('clients.create', $this->extractedClientData);
                            @endphp
                            <p class="mt-1 text-xs text-amber-600">{{ __('No matching client found') }} — <a
                                    href="{{ $createClientUrl }}" class="underline">{{ __('Create new client') }}</a>
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Invoice #') }}</label>
                        <input wire:model="invoiceNumber" type="text"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                        @error('invoiceNumber')
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
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Issue Date') }}</label>
                        <input wire:model="issueDate" type="date"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                        @error('issueDate')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Due Date') }}</label>
                        <input wire:model="dueDate" type="date"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                        @error('dueDate')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('VAT Rate') }} (%)</label>
                        <input wire:model.live="vatRate" type="number" step="0.01" min="0" max="100"
                            class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="border:1px solid #e5e7eb;color:#111827;">
                    </div>
                </div>
            </div>

            {{-- Line items --}}
            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Line Items') }}</h2>
                    <button type="button" wire:click="addItem"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 transition-colors">+
                        {{ __('Add Row') }}</button>
                </div>

                {{-- Column headers --}}
                <div class="flex items-center gap-2 mb-1 px-0.5">
                    <span class="flex-1 text-xs font-semibold text-gray-400">{{ __('Description') }}</span>
                    <span class="w-20 text-xs font-semibold text-gray-400 text-center">{{ __('Qty') }}</span>
                    <span class="w-28 text-xs font-semibold text-gray-400 text-center">{{ __('Unit Price') }}</span>
                    <span class="w-6"></span>
                </div>

                <div class="space-y-2">
                    @foreach ($items as $i => $item)
                        <div class="flex items-center gap-2">
                            <input wire:model="items.{{ $i }}.description" type="text"
                                placeholder="{{ __('Description') }}"
                                class="flex-1 px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                style="border:1px solid #e5e7eb;color:#111827;">
                            <input wire:model.live="items.{{ $i }}.quantity" type="number" min="0.001"
                                step="0.01"
                                class="w-20 px-2 py-2.5 text-sm rounded-xl text-center focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                style="border:1px solid #e5e7eb;color:#111827;">
                            <input wire:model.live="items.{{ $i }}.unit_price" type="number" min="0"
                                step="0.01"
                                class="w-28 px-2 py-2.5 text-sm rounded-xl text-right focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                style="border:1px solid #e5e7eb;color:#111827;">
                            <button type="button" wire:click="removeItem({{ $i }})"
                                class="w-6 h-6 flex items-center justify-center text-gray-300 hover:text-red-500 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        @error('items.' . $i . '.description')
                            <p class="text-xs text-red-500 -mt-1 pl-1">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Notes') }}</label>
                <textarea wire:model="notes" rows="3"
                    class="w-full px-3 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                    style="border:1px solid #e5e7eb;color:#111827;"></textarea>
            </div>
        </div>

        {{-- Sidebar: totals + source doc --}}
        <div class="space-y-5">

            {{-- Totals --}}
            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">{{ __('Summary') }}</h2>
                <div class="space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('Subtotal') }}</span>
                        <span class="font-medium text-gray-900">{{ $currency }}
                            {{ number_format($this->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('VAT') }} ({{ $vatRate }}%)</span>
                        <span class="font-medium text-gray-900">{{ $currency }}
                            {{ number_format($this->vatAmount, 2) }}</span>
                    </div>
                    <div class="pt-2 flex justify-between" style="border-top:1px solid #f3f4f6;">
                        <span class="text-sm font-bold text-gray-900">{{ __('Total') }}</span>
                        <span class="text-base font-bold text-gray-900">{{ $currency }}
                            {{ number_format($this->total, 2) }}</span>
                    </div>
                </div>

                <button wire:click="confirm" wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all"
                    style="background:#0f1117;color:white;" onmouseover="this.style.background='#1e2130'"
                    onmouseout="this.style.background='#0f1117'">
                    <span wire:loading.remove>{{ __('Confirm Import') }}</span>
                    <span wire:loading>{{ __('Saving...') }}</span>
                </button>
            </div>

            {{-- Source document --}}
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
