<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $this->client && $this->client->exists ? 'Edit Client' : 'New Client' }}
        </h2>
        <a href="{{ route('clients.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to clients
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form wire:submit="save" class="space-y-5">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Client / Company Name <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="name"
                    type="text"
                    placeholder="Acme GmbH"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror"
                />
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    wire:model="email"
                    type="email"
                    placeholder="billing@example.com"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror"
                />
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea
                    wire:model="address"
                    rows="3"
                    placeholder="Street, City, ZIP"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('address') border-red-400 @enderror"
                ></textarea>
                @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Country + Currency --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Country <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model="country"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('country') border-red-400 @enderror"
                    >
                        @foreach($countries as $code => $label)
                            <option value="{{ $code }}">{{ $code }} — {{ $label }}</option>
                        @endforeach
                    </select>
                    @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Currency <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model="currency"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('currency') border-red-400 @enderror"
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
                    EU VAT Number
                    <span class="text-gray-400 font-normal">(optional — for B2B reverse charge)</span>
                </label>
                <input
                    wire:model="vat_number"
                    type="text"
                    placeholder="DE123456789"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('vat_number') border-red-400 @enderror"
                />
                <p class="mt-1 text-xs text-gray-400">Format depends on country (e.g. DE123456789, FR12345678901, BG123456789)</p>
                @error('vat_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('clients.index') }}"
                   class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    {{ $this->client && $this->client->exists ? 'Update Client' : 'Create Client' }}
                </button>
            </div>

        </form>
    </div>
</div>
