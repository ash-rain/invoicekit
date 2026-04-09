<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                {{ $this->client && $this->client->exists ? __('Edit Client') : __('New Client') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $this->client && $this->client->exists ? __('Update client details') : __('Add a new client') }}</p>
        </div>
        <a href="{{ route('clients.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('Back to Clients') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Company Lookup Bar (create mode only) ───────────────────── --}}
    @if (! ($this->client && $this->client->exists))
        <div class="mb-6 overflow-hidden rounded-2xl border border-[#eaecf0] bg-white shadow-sm">

            {{-- Header strip --}}
            <div class="flex items-center justify-between gap-3 border-b border-[#eaecf0] bg-gradient-to-r from-indigo-50/70 to-white px-5 py-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-100">
                        <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ __('Look up by VAT or registration number') }}</p>
                        <p class="text-xs text-gray-400">{{ __('— auto-fills the form below') }}</p>
                    </div>
                </div>
                @if ($remainingLookups !== null)
                    <span class="shrink-0 text-xs text-gray-400">
                        {{ trans_choice(':count AI lookup remaining today|:count AI lookups remaining today', $remainingLookups, ['count' => $remainingLookups]) }}
                    </span>
                @endif
            </div>

            <div class="space-y-3 p-5">
                {{-- Type toggle --}}
                <div class="inline-flex gap-0.5 rounded-lg bg-gray-100 p-0.5">
                    <button type="button" wire:click="$set('lookupType', 'vat')"
                        class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $this->lookupType === 'vat' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('VAT Number') }}
                    </button>
                    <button type="button" wire:click="$set('lookupType', 'registration')"
                        class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $this->lookupType === 'registration' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $registrationNumberLabel }}
                    </button>
                </div>

                {{-- Input + button --}}
                <div class="flex gap-2">
                    <input wire:model="lookupInput"
                        wire:keydown.enter.prevent="lookupCompany"
                        type="text"
                        placeholder="{{ $this->lookupType === 'vat' ? __('e.g. DE123456789 or 203137077') : ($registrationNumberHint !== '' ? 'e.g. '.$registrationNumberHint : __('e.g. DE123456789 or 203137077')) }}"
                        class="flex-1 rounded-xl border border-gray-300 px-3 py-2.5 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <button
                        wire:click="lookupCompany"
                        wire:loading.attr="disabled"
                        wire:target="lookupCompany"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                        <svg wire:loading wire:target="lookupCompany"
                            class="h-4 w-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span wire:loading.remove wire:target="lookupCompany">{{ __('Look up') }}</span>
                        <span wire:loading wire:target="lookupCompany">{{ __('Looking up…') }}</span>
                    </button>
                </div>

                {{-- Error message --}}
                @if ($lookupError)
                    <p class="text-sm text-red-600">{{ $lookupError }}</p>
                @endif

                {{-- Source badge (hidden while a new lookup is in flight) --}}
                @if ($lookupSource === 'vies')
                    <div wire:loading.remove wire:target="lookupCompany"
                        class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-700">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Verified via EU VIES — VAT-registered company') }}</span>
                    </div>
                @elseif ($lookupSource === 'gemini')
                    <div wire:loading.remove wire:target="lookupCompany"
                        class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('AI-sourced — please verify the details before saving') }}</span>
                    </div>
                @endif

                {{-- Duplicate warning --}}
                @if ($existingClientId)
                    <div class="flex items-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs text-orange-700">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>
                            {{ __('A client with this number already exists.') }}
                            <a href="{{ route('clients.edit', $existingClientId) }}" class="font-medium underline">{{ __('View existing client →') }}</a>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
        <form wire:submit="save" class="space-y-5">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Client / Company Name') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="name" type="text" placeholder="{{ __('Acme GmbH') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror" />
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                <input wire:model="email" type="email" placeholder="{{ __('billing@example.com') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror" />
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                <textarea wire:model="address" rows="3" placeholder="{{ __('Street, City, ZIP') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('address') border-red-400 @enderror"></textarea>
                @error('address')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Country + Currency --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Country') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="country"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('country') border-red-400 @enderror">
                        @foreach ($countries as $code => $label)
                            <option value="{{ $code }}">{{ $code }} — {{ $label }}</option>
                        @endforeach
                    </select>
                    @error('country')
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

            {{-- VAT Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('EU VAT Number') }}
                    <span class="text-gray-400 font-normal">{{ __('(optional — for B2B reverse charge)') }}</span>
                </label>
                <input wire:model="vat_number" type="text" placeholder="{{ __('DE123456789') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('vat_number') border-red-400 @enderror" />
                <p class="mt-1 text-xs text-gray-400">
                    {{ __('Format depends on country (e.g. DE123456789, FR12345678901, BG123456789)') }}</p>
                @error('vat_number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Registration Number — label and hint are country-specific --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $registrationNumberLabel }}
                    <span class="text-gray-400 font-normal">{{ __('(optional)') }}</span>
                </label>
                <input wire:model="registration_number" type="text"
                    placeholder="{{ $registrationNumberHint ?: __('Company reg. number') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('registration_number') border-red-400 @enderror" />
                @error('registration_number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Default Invoice Language --}}
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Default Invoice Language') }}</label>
                <p class="text-xs text-gray-400 mb-1.5">
                    {{ __('PDF invoices to this client will use this language by default') }}</p>
                <select wire:model="defaultLanguage"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— {{ __('Use my language preference') }} —</option>
                    @foreach ($supportedLanguages as $code)
                        @php $localeData = $localeNames[$code] ?? ['flag' => '', 'name' => strtoupper($code)]; @endphp
                        <option value="{{ $code }}">{{ $localeData['flag'] }} {{ $localeData['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('clients.index') }}"
                    class="px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                    {{ $this->client && $this->client->exists ? __('Update Client') : __('Create Client') }}
                </button>
            </div>

        </form>
    </div>
</div>

        </form>
    </div>
</div>
