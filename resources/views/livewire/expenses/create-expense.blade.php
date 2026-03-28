<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                {{ $this->expense && $this->expense->exists ? __('Edit Expense') : __('New Expense') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Track spending for your business.') }}</p>
        </div>
        <a href="{{ route('expenses.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('Back') }}
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">

        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Description --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Description') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="description" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
                    placeholder="{{ __('What was this expense for?') }}" />
                @error('description')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Amount') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="amount" type="number" step="0.01" min="0"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-400 @enderror"
                    placeholder="0.00" />
                @error('amount')
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

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                <select wire:model="category"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="software">{{ __('Software') }}</option>
                    <option value="hardware">{{ __('Hardware') }}</option>
                    <option value="travel">{{ __('Travel') }}</option>
                    <option value="hosting">{{ __('Hosting') }}</option>
                    <option value="marketing">{{ __('Marketing') }}</option>
                    <option value="other">{{ __('Other') }}</option>
                </select>
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Date') }} <span class="text-red-500">*</span>
                </label>
                <input wire:model="date" type="date"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('date') border-red-400 @enderror" />
                @error('date')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Client --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client (optional)') }}</label>
                <select wire:model="clientId"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Project --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Project (optional)') }}</label>
                <select wire:model="projectId"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($this->projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Billable toggle --}}
            <div class="md:col-span-2 flex items-center gap-3">
                <input wire:model="billable" type="checkbox" id="billable"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="billable" class="text-sm font-medium text-gray-700">{{ __('Billable to client') }}</label>
            </div>

            {{-- Receipt upload --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Receipt (optional)') }}</label>
                <input wire:model="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('receipt')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                @if ($this->expense?->receipt_file)
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Current receipt:') }}
                        <a href="{{ $this->expense->receiptUrl() }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                    </p>
                @endif
            </div>

        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('expenses.index') }}"
                class="px-4 py-2.5 text-sm font-semibold border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                {{ __('Cancel') }}
            </a>
            <button type="submit"
                class="px-5 py-2.5 text-sm font-bold text-white rounded-xl transition"
                style="background:#0f1117;"
                onmouseover="this.style.background='#1e2130'" onmouseout="this.style.background='#0f1117'">
                {{ $this->expense && $this->expense->exists ? __('Update Expense') : __('Create Expense') }}
            </button>
        </div>

    </form>
</div>
