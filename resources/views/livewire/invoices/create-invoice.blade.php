<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                @if ($this->invoice && $this->invoice->exists)
                    {{ __('Edit Invoice') }}
                @elseif($documentType === 'credit_note')
                    {{ __('New Credit Note') }}
                @elseif($documentType === 'debit_note')
                    {{ __('New Debit Note') }}
                @elseif($documentType === 'proforma')
                    {{ __('New Proforma Invoice') }}
                @else
                    {{ __('New Invoice') }}
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $this->invoice && $this->invoice->exists ? __('Update invoice details') : __('Fill in the details below') }}
            </p>
        </div>
        <a href="{{ route('invoices.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('Back to Invoices') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($vatExemptActive)
        <div
            class="mb-6 p-4 rounded-xl border flex items-start gap-3
            {{ $vatExemptOverride ? 'bg-gray-50 border-gray-200 text-gray-600' : 'bg-yellow-50 border-yellow-200 text-yellow-800' }}">
            <span class="text-base leading-none mt-0.5">{{ $vatExemptOverride ? '💡' : '⚠️' }}</span>
            <div class="text-sm">
                @if ($vatExemptOverride)
                    <strong>{{ __('VAT exemption disabled for this invoice.') }}</strong>
                    {{ __('VAT will be calculated normally.') }}
                @else
                    <strong>{{ __('Small-business VAT exemption is active.') }}</strong>
                    {{ __('No VAT will be applied to this invoice.') }}
                @endif
                <a href="{{ route('settings.index') }}" class="underline ml-1">{{ __('Manage in Settings') }}</a>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Header card --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Invoice Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Invoice Number') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="invoiceNumber" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('invoiceNumber') border-red-400 @enderror" />
                @error('invoiceNumber')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Document Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Document Type') }}</label>
                <select wire:model.live="documentType"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="invoice">{{ __('Invoice') }}</option>
                    <option value="credit_note">{{ __('Credit Note') }}</option>
                    <option value="debit_note">{{ __('Debit Note') }}</option>
                    <option value="proforma">{{ __('Proforma Invoice') }}</option>
                </select>
            </div>

            {{-- Language --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('PDF Language') }}</label>
                <select wire:model="language"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach ($supportedLanguages as $code)
                        @php $localeData = $localeNames[$code] ?? ['flag' => '', 'name' => strtoupper($code)]; @endphp
                        <option value="{{ $code }}">{{ $localeData['flag'] }} {{ $localeData['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- PDF Template --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('PDF Template') }}</label>
                <select wire:model="invoiceTemplate"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('invoiceTemplate') border-red-400 @enderror">
                    @foreach ($templates as $slug => $meta)
                        <option value="{{ $slug }}">{{ $meta['name'] }}</option>
                    @endforeach
                </select>
                @error('invoiceTemplate')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Client --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Client') }} <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="clientId"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('clientId') border-red-400 @enderror">
                    <option value="">{{ __('— Select a client —') }}</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->country }})</option>
                    @endforeach
                </select>
                @error('clientId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror

                @if ($selected)
                    <div class="mt-2 p-3 bg-indigo-50 rounded-xl text-xs text-indigo-700 space-y-0.5">
                        <p><strong>{{ $selected->name }}</strong> · {{ $selected->country }}</p>
                        @if ($selected->vat_number)
                            <p>{{ __('VAT:') }} {{ $selected->vat_number }}</p>
                        @endif
                        @if ($selected->address)
                            <p>{{ $selected->address }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Issue Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Issue Date') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="issueDate" type="date"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('issueDate') border-red-400 @enderror" />
                @error('issueDate')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Due Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Due Date') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="dueDate" type="date"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('dueDate') border-red-400 @enderror" />
                @error('dueDate')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Currency --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
                <select wire:model="currency"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach (['EUR', 'USD', 'BGN', 'RON', 'PLN', 'CZK', 'HUF'] as $cur)
                        <option value="{{ $cur }}">{{ $cur }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tax Event Date (BG compliance: Дата на данъчното събитие) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tax Event Date') }}</label>
                <input wire:model="taxEventDate" type="date"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('taxEventDate') border-red-400 @enderror" />
                @error('taxEventDate')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Original invoice reference (credit/debit notes) --}}
            @if (in_array($documentType, ['credit_note', 'debit_note']))
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('To Invoice No.') }} <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="originalInvoiceId" type="number"
                        placeholder="{{ __('Original invoice ID') }}"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('originalInvoiceId') border-red-400 @enderror" />
                    @error('originalInvoiceId')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Issued By / Received By (Съставил / Получил) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Issued By') }}</label>
                <input wire:model="issuedByName" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Received By') }}</label>
                <input wire:model="receivedByName" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            {{-- Proforma notice --}}
            @if ($documentType === 'proforma')
                <div class="md:col-span-2 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-700">
                    {{ __('PROFORMA — Not a tax document') }}
                </div>
            @endif

        </div>

        {{-- Line items --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#eaecf0]">
                <h3 class="text-sm font-bold text-gray-900">{{ __('Line Items') }}</h3>
            </div>

            <table class="min-w-full">
                <thead class="bg-[#fafafa]">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('Description') }}</th>
                        <th
                            class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400 w-24">
                            {{ __('Unit') }}</th>
                        <th
                            class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-gray-400 w-24">
                            {{ __('Qty') }}</th>
                        <th
                            class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-gray-400 w-32">
                            {{ __('Unit Price') }}</th>
                        <th
                            class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-gray-400 w-32">
                            {{ __('Line Total') }}</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->items as $i => $item)
                        <tr class="border-t border-[#f3f4f6]" wire:key="item-{{ $i }}">
                            <td class="px-4 py-2.5">
                                <input wire:model.live="items.{{ $i }}.description" type="text"
                                    placeholder="{{ __('Service description') }}"
                                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 @error('items.' . $i . '.description') border-red-400 @enderror" />
                                @error('items.' . $i . '.description')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-2.5">
                                <input wire:model="items.{{ $i }}.unit" type="text"
                                    placeholder="{{ __('pcs.') }}"
                                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </td>
                            <td class="px-4 py-2.5">
                                <input wire:model.live="items.{{ $i }}.quantity" type="number"
                                    step="0.01" min="0.01"
                                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400 @error('items.' . $i . '.quantity') border-red-400 @enderror" />
                            </td>
                            <td class="px-4 py-2.5">
                                <input wire:model.live="items.{{ $i }}.unit_price" type="number"
                                    step="0.01" min="0"
                                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400 @error('items.' . $i . '.unit_price') border-red-400 @enderror" />
                            </td>
                            <td class="px-4 py-2.5 text-right text-sm font-medium text-gray-700">
                                {{ number_format((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0), 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if (count($this->items) > 1)
                                    <button type="button" wire:click="removeItem({{ $i }})"
                                        class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-3.5 border-t border-[#f3f4f6]">
                <button type="button" wire:click="addItem"
                    class="text-sm font-semibold text-[#0f1117] hover:text-gray-500">
                    {{ __('+ Add line item') }}
                </button>
            </div>
        </div>

        {{-- Totals + VAT --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Notes --}}
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Notes / Payment Terms') }}</label>
                <textarea wire:model="notes" rows="5"
                    placeholder="{{ __('Bank details, payment instructions, thank you message...') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            {{-- VAT Summary --}}
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">{{ __('Summary') }}</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="font-medium">{{ $currency }} {{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-gray-600">
                        <span>
                            {{ __('VAT') }}
                            @if ($vatRate > 0)
                                ({{ $vatRate }}%)
                            @endif
                        </span>
                        <span class="font-medium">{{ $currency }} {{ number_format($vatAmount, 2) }}</span>
                    </div>

                    @if ($vatType !== 'standard')
                        <div
                            class="text-xs px-2.5 py-1.5 rounded-lg
                            @if ($vatType === 'reverse_charge') bg-yellow-50 text-yellow-700
                            @elseif($vatType === 'oss') bg-blue-50 text-blue-700
                            @elseif($vatType === 'vat_exempt') bg-yellow-50 text-yellow-800
                            @else bg-gray-50 text-gray-600 @endif">
                            @if ($vatType === 'reverse_charge')
                                ⚡ {{ __('Reverse charge — buyer accounts for VAT') }}
                            @elseif($vatType === 'oss')
                                🌍 {{ __("OSS rate applied (seller's country rate)") }}
                            @elseif($vatType === 'exempt')
                                ✓ {{ __('VAT exempt (non-EU buyer)') }}
                            @elseif($vatType === 'vat_exempt')
                                🔖 {{ __('Small-business VAT exemption applied') }}
                            @endif
                        </div>
                    @endif

                    @if ($vatExemptActive)
                        <div class="pt-2 border-t border-[#eaecf0]">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="vatExemptOverride"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                {{ __('Override: apply VAT for this invoice') }}
                            </label>
                        </div>
                    @endif

                    <div
                        class="flex justify-between text-[#0f1117] font-bold text-base border-t border-[#eaecf0] pt-3 mt-1">
                        <span>{{ __('Total') }}</span>
                        <span>{{ $currency }} {{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-4">
            <a href="{{ route('invoices.index') }}"
                class="px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">
                {{ __('Cancel') }}
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                {{ $this->invoice && $this->invoice->exists ? __('Update Invoice') : __('Create Invoice') }}
            </button>
        </div>

    </form>
</div>
