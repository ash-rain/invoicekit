<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                {{ $this->client && $this->client->exists ? __('Edit Client') : __('New Client') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $this->client && $this->client->exists ? __('Update client details') : __('Add a new client') }}</p>
        </div>
        <a href="{{ route('clients.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to Clients') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
        <form wire:submit="save" class="space-y-5">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Client / Company Name') }} <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="name"
                    type="text"
                    placeholder="{{ __('Acme GmbH') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror"
                />
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                <input
                    wire:model="email"
                    type="email"
                    placeholder="{{ __('billing@example.com') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror"
                />
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                <textarea
                    wire:model="address"
                    rows="3"
                    placeholder="{{ __('Street, City, ZIP') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('address') border-red-400 @enderror"
                ></textarea>
                @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Country + Currency --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Country') }} <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model="country"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('country') border-red-400 @enderror"
                    >
                        @foreach($countries as $code => $label)
                            <option value="{{ $code }}">{{ $code }} — {{ $label }}</option>
                        @endforeach
                    </select>
                    @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Currency') }} <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model="currency"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('currency') border-red-400 @enderror"
                    >
                        @foreach($currencies as $cur)
                            <option value="{{ $cur }}">{{ $cur }}</option>
                        @endforeach
                    </select>
                    @error('currency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- VAT Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('EU VAT Number') }}
                    <span class="text-gray-400 font-normal">{{ __('(optional — for B2B reverse charge)') }}</span>
                </label>
                <input
                    wire:model="vat_number"
                    type="text"
                    placeholder="{{ __('DE123456789') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('vat_number') border-red-400 @enderror"
                />
                <p class="mt-1 text-xs text-gray-400">{{ __('Format depends on country (e.g. DE123456789, FR12345678901, BG123456789)') }}</p>
                @error('vat_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Default Invoice Language --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Default Invoice Language') }}</label>
                <p class="text-xs text-gray-400 mb-1.5">{{ __('PDF invoices to this client will use this language by default') }}</p>
                <select wire:model="defaultLanguage"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— {{ __('Use my language preference') }} —</option>
                    @foreach ($supportedLanguages as $code)
                        @php $localeData = $localeNames[$code] ?? ['flag' => '', 'name' => strtoupper($code)]; @endphp
                        <option value="{{ $code }}">{{ $localeData['flag'] }} {{ $localeData['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('clients.index') }}"
                   class="px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]"
                >
                    {{ $this->client && $this->client->exists ? __('Update Client') : __('Create Client') }}
                </button>
            </div>

        </form>
    </div>
</div>
