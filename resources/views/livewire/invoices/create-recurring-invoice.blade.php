<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                {{ $this->recurringInvoice && $this->recurringInvoice->exists ? __('Edit Recurring Template') : __('New Recurring Template') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Invoices will be auto-generated on the selected schedule.') }}
            </p>
        </div>
        <a href="{{ route('recurring-invoices.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('Back') }}
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Header card --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Client --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Client') }} <span class="text-red-500">*</span>
                </label>
                <select wire:model="clientId"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('clientId') border-red-400 @enderror">
                    <option value="">{{ __('Select client…') }}</option>
                    @foreach ($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('clientId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Frequency --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Frequency') }} <span
                        class="text-red-500">*</span></label>
                <select wire:model="frequency"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="monthly">{{ __('Monthly') }}</option>
                    <option value="quarterly">{{ __('Quarterly') }}</option>
                    <option value="annually">{{ __('Annually') }}</option>
                </select>
            </div>

            {{-- Next Send Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Next Send Date') }} <span
                        class="text-red-500">*</span></label>
                <input wire:model="nextSendDate" type="date"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('nextSendDate') border-red-400 @enderror" />
                @error('nextSendDate')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Currency --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
                <select wire:model="currency"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="EUR">EUR — Euro</option>
                    <option value="USD">USD — US Dollar</option>
                    <option value="GBP">GBP — British Pound</option>
                    <option value="BGN">BGN — Bulgarian Lev</option>
                    <option value="PLN">PLN — Polish Zloty</option>
                    <option value="CZK">CZK — Czech Koruna</option>
                    <option value="HUF">HUF — Hungarian Forint</option>
                    <option value="RON">RON — Romanian Leu</option>
                    <option value="SEK">SEK — Swedish Krona</option>
                    <option value="DKK">DKK — Danish Krone</option>
                </select>
            </div>

            {{-- VAT Rate --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('VAT Rate (%)') }}</label>
                <input wire:model.live="vatRate" type="number" step="0.01" min="0" max="100"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            {{-- Notes --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                <textarea wire:model="notes" rows="3"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="{{ __('Default notes for generated invoices…') }}"></textarea>
            </div>
        </div>

        {{-- Line items --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
            <h2 class="text-sm font-bold text-gray-700 mb-4">{{ __('Line Items') }}</h2>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div class="grid grid-cols-12 gap-3 items-start" wire:key="item-{{ $index }}">
                        <div class="col-span-6">
                            @if ($loop->first)
                                <label
                                    class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Description') }}</label>
                            @endif
                            <input wire:model.live="items.{{ $index }}.description" type="text"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('items.' . $index . '.description') border-red-400 @enderror"
                                placeholder="{{ __('Description') }}" />
                        </div>
                        <div class="col-span-2">
                            @if ($loop->first)
                                <label
                                    class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Qty') }}</label>
                            @endif
                            <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01"
                                min="0.01"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="col-span-3">
                            @if ($loop->first)
                                <label
                                    class="block text-xs font-semibold text-gray-500 mb-1">{{ __('Unit Price') }}</label>
                            @endif
                            <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01"
                                min="0"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="col-span-1 {{ $loop->first ? 'pt-6' : '' }} flex items-center">
                            @if (count($items) > 1)
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="addItem"
                class="mt-4 text-sm text-indigo-600 font-medium hover:text-indigo-800 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add Line Item') }}
            </button>
        </div>

        {{-- Totals + Submit --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="w-full sm:w-64 bg-white rounded-2xl border border-[#eaecf0] p-5 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>{{ __('Subtotal') }}</span>
                    <span>{{ formatCurrency($currency, $subtotal) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>{{ __('VAT') }} ({{ $vatRate }}%)</span>
                    <span>{{ formatCurrency($currency, $vatAmount) }}</span>
                </div>
                <div class="flex justify-between font-bold text-[#0f1117] pt-2 border-t border-[#eaecf0]">
                    <span>{{ __('Total') }}</span>
                    <span>{{ formatCurrency($currency, $total) }}</span>
                </div>
            </div>

            <div class="flex gap-3 w-full sm:w-auto">
                <a href="{{ route('recurring-invoices.index') }}"
                    class="px-5 py-2.5 text-sm font-semibold border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-bold bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] transition">
                    {{ $this->recurringInvoice && $this->recurringInvoice->exists ? __('Update Template') : __('Create Template') }}
                </button>
            </div>
        </div>

    </form>
</div>
