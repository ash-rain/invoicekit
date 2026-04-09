<div>
    @if (session('payment_method_success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('payment_method_success') }}
        </div>
    @endif

    @if (session('payment_method_error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            {{ session('payment_method_error') }}
        </div>
    @endif

    {{-- Payment Methods List --}}
    <div class="space-y-3">
        @forelse ($paymentMethods as $method)
            <div class="flex items-center justify-between rounded-xl border border-[#eaecf0] bg-white px-4 py-3 {{ $method->is_default ? 'ring-1 ring-indigo-200' : '' }}">
                <div class="flex items-center gap-3">
                    {{-- Type icon --}}
                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ match($method->type) { 'bank_transfer' => 'bg-blue-50', 'stripe' => 'bg-purple-50', 'cash' => 'bg-green-50', default => 'bg-gray-50' } }}">
                        @if ($method->type === 'bank_transfer')
                            <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-4 9 4v2H3V6zm1 4h16v2H4v-2zm2 4h12v6H6v-6zm2 0v6m4-6v6m4-6v6"/></svg>
                        @elseif ($method->type === 'stripe')
                            <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        @elseif ($method->type === 'cash')
                            <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @endif
                    </span>

                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-900">{{ $method->displayLabel() }}</p>
                            @if ($method->is_default)
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ __('Default') }}</span>
                            @endif
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                {{ match($method->type) { 'bank_transfer' => __('Bank Transfer'), 'stripe' => 'Stripe', 'cash' => __('Cash'), default => $method->type } }}
                            </span>
                        </div>
                        @if ($method->type === 'bank_transfer' && $method->bank_iban)
                            <p class="text-xs text-gray-500 font-mono mt-0.5">IBAN: {{ $method->bank_iban }}@if ($method->bank_bic) &middot; BIC: {{ $method->bank_bic }}@endif</p>
                        @endif
                        @if ($method->notes)
                            <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($method->notes, 80) }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    @if (!$method->is_default)
                        <button wire:click="setDefault({{ $method->id }})" type="button"
                            class="px-2.5 py-1 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            {{ __('Set as Default') }}
                        </button>
                    @endif
                    @if ($method->type !== 'stripe')
                        <button wire:click="edit({{ $method->id }})" type="button"
                            class="px-2.5 py-1 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            {{ __('Edit') }}
                        </button>
                        <button wire:click="confirmDelete({{ $method->id }})" type="button"
                            class="px-2.5 py-1 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                            {{ __('Delete') }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-sm text-gray-400">
                <p>{{ __('No payment methods added yet.') }}</p>
                <p class="mt-1 text-xs">{{ __('Add a bank account, Stripe, or cash payment method for your invoices.') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Add button --}}
    @if (!$showForm)
        <div class="mt-4 flex items-center justify-between">
            <button wire:click="add" type="button" @if(!$canAdd) disabled @endif
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl transition {{ $canAdd ? 'bg-[#0f1117] text-white hover:bg-[#1a1f2e]' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add Payment Method') }}
            </button>
            @if ($remaining !== null)
                <span class="text-xs text-gray-400">
                    {{ trans_choice(':count payment method remaining|:count payment methods remaining', $remaining, ['count' => $remaining]) }}
                </span>
            @endif
        </div>
    @endif

    {{-- Delete confirmation --}}
    @if ($confirmingDelete)
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-sm text-red-800 font-medium">{{ __('Delete payment method?') }}</p>
            <p class="text-xs text-red-600 mt-1">{{ __('This action cannot be undone. Invoices using this method will fall back to the company default.') }}</p>
            <div class="flex gap-2 mt-3">
                <button wire:click="delete" type="button"
                    class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                    {{ __('Delete') }}
                </button>
                <button wire:click="cancelDelete" type="button"
                    class="px-3 py-1.5 text-xs font-semibold text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Add/Edit form --}}
    @if ($showForm)
        <div class="mt-4 p-5 bg-gray-50 border border-[#eaecf0] rounded-xl space-y-4">
            <h4 class="text-sm font-bold text-gray-900">
                {{ $editingId ? __('Edit Payment Method') : __('Add Payment Method') }}
            </h4>

            @if (!$editingId)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment method type') }}</label>
                    <select wire:model.live="type"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        <option value="cash">{{ __('Cash') }}</option>
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Label') }}</label>
                <input wire:model="label" type="text" placeholder="{{ __('e.g. Main EUR Account') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                @error('label') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @if ($type === 'bank_transfer')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bank Name') }}</label>
                    <input wire:model="bankName" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    @error('bankName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('IBAN') }} <span class="text-red-400">*</span></label>
                        <input wire:model="bankIban" type="text"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('bankIban') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('BIC / SWIFT') }}</label>
                        <input wire:model="bankBic" type="text"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('bankBic') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                <textarea wire:model="notes" rows="2" placeholder="{{ __('e.g. Payment instructions or reference details') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2 pt-1">
                <button wire:click="save" type="button"
                    class="px-4 py-2 text-sm font-semibold bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] transition">
                    {{ __('Save') }}
                </button>
                <button wire:click="cancel" type="button"
                    class="px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    @endif
</div>
