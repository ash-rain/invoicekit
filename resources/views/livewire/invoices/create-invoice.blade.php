<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $this->invoice && $this->invoice->exists ? 'Edit Invoice' : 'New Invoice' }}
        </h2>
        <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to invoices
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Header card --}}
        <div class="bg-white rounded-xl shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Invoice Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Invoice Number <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="invoiceNumber"
                    type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('invoiceNumber') border-red-400 @enderror"
                />
                @error('invoiceNumber') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Language --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PDF Language</label>
                <select
                    wire:model="language"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="en">English</option>
                    <option value="bg">Bulgarian (Български)</option>
                </select>
            </div>

            {{-- Client --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Client <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model.live="clientId"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('clientId') border-red-400 @enderror"
                >
                    <option value="">— Select a client —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->country }})</option>
                    @endforeach
                </select>
                @error('clientId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                @if($selected)
                    <div class="mt-2 p-3 bg-indigo-50 rounded-lg text-xs text-indigo-700 space-y-0.5">
                        <p><strong>{{ $selected->name }}</strong> · {{ $selected->country }}</p>
                        @if($selected->vat_number)
                            <p>VAT: {{ $selected->vat_number }}</p>
                        @endif
                        @if($selected->address)
                            <p>{{ $selected->address }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Issue Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Issue Date <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="issueDate"
                    type="date"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('issueDate') border-red-400 @enderror"
                />
                @error('issueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Due Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Due Date <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="dueDate"
                    type="date"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('dueDate') border-red-400 @enderror"
                />
                @error('dueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Currency --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select
                    wire:model="currency"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    @foreach(['EUR','USD','BGN','RON','PLN','CZK','HUF'] as $cur)
                        <option value="{{ $cur }}">{{ $cur }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- Line items --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Line Items</h3>
            </div>

            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-32">Unit Price</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-32">Line Total</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->items as $i => $item)
                        <tr>
                            <td class="px-4 py-2">
                                <input
                                    wire:model.live="items.{{ $i }}.description"
                                    type="text"
                                    placeholder="Service description"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 @error('items.'.$i.'.description') border-red-400 @enderror"
                                />
                                @error('items.'.$i.'.description') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    wire:model.live="items.{{ $i }}.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400 @error('items.'.$i.'.quantity') border-red-400 @enderror"
                                />
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    wire:model.live="items.{{ $i }}.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400 @error('items.'.$i.'.unit_price') border-red-400 @enderror"
                                />
                            </td>
                            <td class="px-4 py-2 text-right text-sm font-medium text-gray-700">
                                {{ number_format((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0), 2) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if(count($this->items) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $i }})"
                                        class="text-red-400 hover:text-red-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-3 border-t border-gray-100">
                <button
                    type="button"
                    wire:click="addItem"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                >
                    + Add line item
                </button>
            </div>
        </div>

        {{-- Totals + VAT --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Notes --}}
            <div class="bg-white rounded-xl shadow p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes / Payment Terms</label>
                <textarea
                    wire:model="notes"
                    rows="4"
                    placeholder="Bank details, payment instructions, thank you message..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>

            {{-- VAT Summary --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Summary</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium">{{ $currency }} {{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-gray-600">
                        <span>
                            VAT
                            @if($vatRate > 0)
                                ({{ $vatRate }}%)
                            @endif
                        </span>
                        <span class="font-medium">{{ $currency }} {{ number_format($vatAmount, 2) }}</span>
                    </div>

                    @if($vatType !== 'standard')
                        <div class="text-xs px-2 py-1 rounded
                            @if($vatType === 'reverse_charge') bg-yellow-50 text-yellow-700
                            @elseif($vatType === 'oss') bg-blue-50 text-blue-700
                            @else bg-gray-50 text-gray-600 @endif">
                            @if($vatType === 'reverse_charge')
                                ⚡ Reverse charge — buyer accounts for VAT
                            @elseif($vatType === 'oss')
                                🌍 OSS rate applied (seller's country rate)
                            @elseif($vatType === 'exempt')
                                ✓ VAT exempt (non-EU buyer)
                            @endif
                        </div>
                    @endif

                    <div class="flex justify-between text-gray-900 font-semibold text-base border-t border-gray-200 pt-2 mt-2">
                        <span>Total</span>
                        <span>{{ $currency }} {{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('invoices.index') }}"
               class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button
                type="submit"
                class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                {{ $this->invoice && $this->invoice->exists ? 'Update Invoice' : 'Create Invoice' }}
            </button>
        </div>

    </form>
</div>
