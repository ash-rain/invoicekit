<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-white flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        {{-- Progress bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                @foreach ([1 => __('Company'), 2 => __('First Client'), 3 => __('First Project')] as $n => $label)
                    <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $step >= $n ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $n }}
                        </div>
                        <span
                            class="ml-2 text-xs font-medium {{ $step >= $n ? 'text-indigo-700' : 'text-gray-400' }} hidden sm:inline">
                            {{ $label }}
                        </span>
                        @if (!$loop->last)
                            <div class="flex-1 h-0.5 mx-3 {{ $step > $n ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900">
                    @if ($step === 1)
                        {{ __('Welcome to InvoiceKit 👋') }}
                    @elseif ($step === 2)
                        {{ __('Add Your First Client') }}
                    @else
                        {{ __('Set Up Your First Project') }}
                    @endif
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    @if ($step === 1)
                        {{ __('Tell us a bit about your business.') }}
                    @elseif ($step === 2)
                        {{ __('Who are you invoicing?') }}
                    @else
                        {{ __('Optional: create a project to start tracking time.') }}
                    @endif
                </p>
            </div>

            {{-- Step 1: Company Info --}}
            @if ($step === 1)
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Your Name / Company Name') }}</label>
                        <input wire:model="companyName" type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="ACME Freelance Ltd.">
                        @error('companyName')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Your Country') }}</label>
                        <select wire:model="companyCountry"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach ($countries as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('companyCountry')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button wire:click="nextStep"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        {{ __('Continue →') }}
                    </button>
                </div>
            @endif

            {{-- Step 2: First Client --}}
            @if ($step === 2)
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client Name') }}</label>
                        <input wire:model="clientName" type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="TechCorp GmbH">
                        @error('clientName')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client Email') }} <span
                                class="text-gray-400">{{ __('(optional)') }}</span></label>
                        <input wire:model="clientEmail" type="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="billing@techcorp.de">
                        @error('clientEmail')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Country') }}</label>
                            <select wire:model="clientCountry"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach ($countries as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
                            <select wire:model="clientCurrency"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-between">
                    <button wire:click="previousStep"
                        class="px-4 py-2 text-gray-600 hover:text-gray-900 text-sm font-medium">
                        {{ __('← Back') }}
                    </button>
                    <button wire:click="nextStep"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        {{ __('Continue →') }}
                    </button>
                </div>
            @endif

            {{-- Step 3: First Project --}}
            @if ($step === 3)
                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-3 bg-indigo-50 rounded-lg">
                        <input wire:model.live="skipInvoice" type="checkbox" id="skip"
                            class="mt-0.5 w-4 h-4 rounded text-indigo-600">
                        <label for="skip" class="text-sm text-gray-700 cursor-pointer">
                            {{ __('Skip for now — I\'ll set up a project later') }}
                        </label>
                    </div>

                    @if (!$skipInvoice)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Project Name') }}</label>
                            <input wire:model="projectName" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Website Redesign">
                            @error('projectName')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Hourly Rate (:currency)', ['currency' => $clientCurrency]) }}</label>
                            <input wire:model="hourlyRate" type="number" step="0.01" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="75.00">
                            @error('hourlyRate')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="mt-6 flex justify-between">
                    <button wire:click="previousStep"
                        class="px-4 py-2 text-gray-600 hover:text-gray-900 text-sm font-medium">
                        {{ __('← Back') }}
                    </button>
                    <button wire:click="complete"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        {{ __('🚀 Launch InvoiceKit') }}
                    </button>
                </div>
            @endif
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            {!! __('By continuing you agree to our :terms and :privacy.', [
                'terms' => '<a href="' . url('/terms') . '" class="underline">' . __('Terms') . '</a>',
                'privacy' => '<a href="' . url('/privacy') . '" class="underline">' . __('Privacy Policy') . '</a>',
            ]) !!}
        </p>
    </div>
</div>
