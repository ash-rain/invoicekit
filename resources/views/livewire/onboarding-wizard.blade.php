<div class="min-h-screen flex items-center justify-center p-6" style="background:#f5f6fa;">
    <div class="w-full max-w-3xl">
        @php
        $stepLabels = [
            1 => __('Business'),
            2 => __('VAT & Tax'),
            3 => __('Client'),
            4 => __('Project'),
            5 => __('Payment'),
            6 => __('Done'),
        ];
        @endphp

        <div class="flex items-start gap-5">

            {{-- ─── Left: Vertical Stepper ─── --}}
            <div class="w-48 shrink-0">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-5 py-5 border-b border-gray-100">
                        <span class="font-black text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;font-size:1.15rem;">InvoiceKit</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('Setup') }}</p>
                    </div>
                    <div class="px-5 py-5">
                        {{-- Language selector --}}
                        @php
                            $localeNames = config('invoicekit.locale_names', []);
                            $supportedLanguages = config('invoicekit.supported_languages', ['en']);
                            $currentLocale = app()->getLocale();
                        @endphp
                        <form method="POST" action="{{ route('locale.switch') }}" class="mb-4">
                            @csrf
                            <select name="locale" onchange="this.form.submit()"
                                class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-gray-600 bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-400 cursor-pointer">
                                @foreach ($supportedLanguages as $code)
                                    @php $meta = $localeNames[$code] ?? ['flag' => '🌐', 'name' => strtoupper($code)]; @endphp
                                    <option value="{{ $code }}" {{ $currentLocale === $code ? 'selected' : '' }}>
                                        {{ $meta['flag'] }} {{ $meta['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <div class="relative">
                            {{-- Vertical connector line running through all steps --}}
                            <div class="absolute left-[13px] top-[26px] w-0.5 bg-gray-200" style="bottom:26px;"></div>
                            @foreach ($stepLabels as $n => $label)
                                <div class="relative flex items-center gap-3 py-2.5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 relative z-10
                                        {{ $step > $n ? 'bg-green-500 text-white' : ($step === $n ? 'text-[#0f1117]' : 'bg-gray-100 text-gray-400') }}"
                                        style="{{ $step === $n ? 'background:#f59e0b;' : '' }}">
                                        @if ($step > $n)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            {{ $n }}
                                        @endif
                                    </div>
                                    <span class="text-sm font-medium leading-tight
                                        {{ $step === $n ? 'text-[#0f1117] font-semibold' : ($step > $n ? 'text-green-700' : 'text-gray-400') }}">
                                        {{ $label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Right: Form card + footer ─── --}}
            <div class="flex-1 min-w-0">
                <div class="bg-white rounded-2xl shadow-xl p-8">

                    {{-- Step headings --}}
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-[#0f1117]" style="font-family:'Syne',sans-serif;">
                            @if ($step === 1) {{ __('Welcome to InvoiceKit 👋') }}
                            @elseif ($step === 2) {{ __('VAT & Tax Setup') }}
                            @elseif ($step === 3) {{ __('Add Your First Client') }}
                            @elseif ($step === 4) {{ __('Set Up a Project') }}
                            @elseif ($step === 5) {{ __('Payment Method') }}
                            @else {{ __('You\'re all set! 🎉') }}
                            @endif
                        </h1>
                        <p class="text-gray-500 text-sm mt-1.5">
                            @if ($step === 1) {{ __('Tell us a bit about your business.') }}
                            @elseif ($step === 2) {{ __('Your VAT and tax registration details.') }}
                            @elseif ($step === 3) {{ __('Who are you invoicing?') }}
                            @elseif ($step === 4) {{ __('Optional: create a project to start tracking time.') }}
                            @elseif ($step === 5) {{ __('Optional: add a payment method shown on invoices.') }}
                            @else {{ __('Here\'s what you can do next.') }}
                            @endif
                        </p>
                    </div>

                    {{-- ─── Step 1: Business Info ─── --}}
                    @if ($step === 1)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Your Name / Company Name') }}</label>
                                <input wire:model="companyName" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="ACME Freelance Ltd.">
                                @error('companyName') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Your Country') }}</label>
                                <select wire:model.live="companyCountry"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach ($countries as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('companyCountry') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }} <span class="text-gray-400 font-normal text-xs">({{ __('optional') }})</span></label>
                                <input wire:model="companyAddress" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="{{ __('Street address') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }} <span class="text-gray-400 font-normal text-xs">({{ __('optional') }})</span></label>
                                <input wire:model="companyPhone" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="+359 88 123 4567">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button wire:click="nextStep" class="px-6 py-2.5 bg-[#0f1117] text-white rounded-xl text-sm font-bold hover:bg-[#1a1f2e]">
                                {{ __('Continue →') }}
                            </button>
                        </div>
                    @endif

                    {{-- ─── Step 2: VAT & Tax ─── --}}
                    @if ($step === 2)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('VAT Number') }}
                                    @if ($this->isEuCountry && !$vatExempt)
                                        <span class="text-red-500">*</span>
                                    @else
                                        <span class="text-gray-400 font-normal text-xs">({{ __('optional') }})</span>
                                    @endif
                                </label>
                                @if ($this->vatNumberHint)
                                    <p class="text-xs text-indigo-500 mb-1">{{ $this->vatNumberHint }}</p>
                                @endif
                                <input wire:model="vatNumber" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="e.g. {{ strtoupper($companyCountry) }}123456789">
                                @error('vatNumber') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $this->registrationNumberLabel }} <span class="text-gray-400 font-normal text-xs">({{ __('optional') }})</span>
                                </label>
                                <input wire:model="registrationNumber" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="{{ $this->registrationNumberHint }}">
                            </div>
                            @if ($this->isEuCountry)
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <div class="flex items-start gap-3">
                                        <input wire:model.live="vatExempt" type="checkbox" id="vat_exempt"
                                            class="mt-0.5 w-4 h-4 rounded accent-amber-500">
                                        <div>
                                            <label for="vat_exempt" class="text-sm font-medium text-gray-700 cursor-pointer">{{ __('VAT Exempt (small business)') }}</label>
                                            @if ($this->vatExemptThreshold)
                                                <p class="text-xs text-gray-500 mt-0.5">{{ __('Threshold for :country: :threshold', ['country' => $companyCountry, 'threshold' => $this->vatExemptThreshold]) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="mt-6 flex justify-between">
                            <button wire:click="previousStep" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('← Back') }}</button>
                            <button wire:click="nextStep" class="px-6 py-2.5 bg-[#0f1117] text-white rounded-xl text-sm font-bold hover:bg-[#1a1f2e]">{{ __('Continue →') }}</button>
                        </div>
                    @endif

                    {{-- ─── Step 3: First Client ─── --}}
                    @if ($step === 3)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client Name') }}</label>
                                <input wire:model="clientName" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="TechCorp GmbH">
                                @error('clientName') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client Email') }} <span class="text-gray-400 font-normal text-xs">({{ __('optional') }})</span></label>
                                <input wire:model="clientEmail" type="email"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="billing@techcorp.de">
                                @error('clientEmail') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Country') }}</label>
                                    <select wire:model.live="clientCountry"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        @foreach ($countries as $code => $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
                                    <select wire:model="clientCurrency"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Client VAT Number') }} <span class="text-gray-400 font-normal text-xs">({{ __('optional — enables reverse charge') }})</span></label>
                                <input wire:model="clientVatNumber" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="DE123456789">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-between">
                            <button wire:click="previousStep" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('← Back') }}</button>
                            <button wire:click="nextStep" class="px-6 py-2.5 bg-[#0f1117] text-white rounded-xl text-sm font-bold hover:bg-[#1a1f2e]">{{ __('Continue →') }}</button>
                        </div>
                    @endif

                    {{-- ─── Step 4: First Project ─── --}}
                    @if ($step === 4)
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3.5 bg-amber-50 rounded-xl border border-amber-100">
                                <input wire:model.live="skipProject" type="checkbox" id="skip_project" class="mt-0.5 w-4 h-4 rounded text-amber-500">
                                <label for="skip_project" class="text-sm text-gray-700 cursor-pointer">{{ __("Skip for now — I'll set up a project later") }}</label>
                            </div>
                            @if (!$skipProject)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Project Name') }}</label>
                                    <input wire:model="projectName" type="text"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Website Redesign">
                                    @error('projectName') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Hourly Rate (:currency)', ['currency' => $clientCurrency]) }}</label>
                                    <input wire:model="hourlyRate" type="number" step="0.01" min="0"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="75.00">
                                    @error('hourlyRate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>
                        <div class="mt-6 flex justify-between">
                            <button wire:click="previousStep" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('← Back') }}</button>
                            <button wire:click="nextStep" class="px-6 py-2.5 bg-[#0f1117] text-white rounded-xl text-sm font-bold hover:bg-[#1a1f2e]">{{ __('Continue →') }}</button>
                        </div>
                    @endif

                    {{-- ─── Step 5: Payment Method ─── --}}
                    @if ($step === 5)
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3.5 bg-amber-50 rounded-xl border border-amber-100">
                                <input wire:model.live="skipPayment" type="checkbox" id="skip_payment" class="mt-0.5 w-4 h-4 rounded text-amber-500">
                                <label for="skip_payment" class="text-sm text-gray-700 cursor-pointer">{{ __("Skip — I'll add a payment method in Settings") }}</label>
                            </div>
                            @if (!$skipPayment)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Payment Type') }}</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button wire:click="$set('paymentMethodType', 'bank_transfer')" type="button"
                                            class="p-3 rounded-xl border text-sm font-medium text-center transition-colors
                                                {{ $paymentMethodType === 'bank_transfer' ? 'border-amber-400 bg-amber-50 text-amber-800' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                            🏦 {{ __('Bank Transfer') }}
                                        </button>
                                        <button wire:click="$set('paymentMethodType', 'cash')" type="button"
                                            class="p-3 rounded-xl border text-sm font-medium text-center transition-colors
                                                {{ $paymentMethodType === 'cash' ? 'border-amber-400 bg-amber-50 text-amber-800' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                            💵 {{ __('Cash') }}
                                        </button>
                                    </div>
                                </div>
                                @if ($paymentMethodType === 'bank_transfer')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('IBAN') }}
                                            @if ($this->ibanHint)
                                                <span class="text-xs text-indigo-500 font-normal">({{ $this->ibanHint }})</span>
                                            @endif
                                        </label>
                                        <input wire:model="bankIban" type="text"
                                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="BG80 BNBG 9661 1020 3456 78">
                                        @error('bankIban') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('BIC / SWIFT') }} <span class="text-gray-400 font-normal text-xs">({{ __('optional') }})</span></label>
                                        <input wire:model="bankBic" type="text"
                                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="BNBGBGSF">
                                    </div>
                                @endif
                                <p class="text-xs text-gray-400">{{ __('Stripe payments can be connected later in Settings → Payments.') }}</p>
                            @endif
                        </div>
                        <div class="mt-6 flex justify-between">
                            <button wire:click="previousStep" class="px-4 py-2.5 text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('← Back') }}</button>
                            <button wire:click="nextStep" class="px-6 py-2.5 bg-[#0f1117] text-white rounded-xl text-sm font-bold hover:bg-[#1a1f2e]">{{ __('Continue →') }}</button>
                        </div>
                    @endif

                    {{-- ─── Step 6: You're All Set ─── --}}
                    @if ($step === 6)
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('invoices.create') }}"
                                    class="p-4 rounded-xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors text-center">
                                    <div class="text-2xl mb-1">📄</div>
                                    <div class="text-sm font-semibold text-[#0f1117]">{{ __('Create Invoice') }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ __('Bill your first client') }}</div>
                                </a>
                                <a href="{{ route('timer') }}"
                                    class="p-4 rounded-xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors text-center">
                                    <div class="text-2xl mb-1">⏱️</div>
                                    <div class="text-sm font-semibold text-[#0f1117]">{{ __('Start Timer') }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ __('Track your first hours') }}</div>
                                </a>
                                <a href="{{ route('invoices.import') }}"
                                    class="p-4 rounded-xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors text-center">
                                    <div class="text-2xl mb-1">🤖</div>
                                    <div class="text-sm font-semibold text-[#0f1117]">{{ __('Import Document') }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ __('Let AI read your paperwork') }}</div>
                                </a>
                                {{-- Context-aware 4th card --}}
                                @if ($skipPayment)
                                    <a href="{{ route('settings.index') }}?tab=business"
                                        class="p-4 rounded-xl border border-amber-300 bg-amber-50 transition-colors text-center">
                                        <div class="text-2xl mb-1">🏦</div>
                                        <div class="text-sm font-semibold text-amber-800">{{ __('Add Payment Method') }}</div>
                                        <div class="text-xs text-amber-600 mt-0.5">{{ __('So clients know how to pay') }}</div>
                                    </a>
                                @elseif ($skipProject)
                                    <a href="{{ route('projects.index') }}"
                                        class="p-4 rounded-xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors text-center">
                                        <div class="text-2xl mb-1">📁</div>
                                        <div class="text-sm font-semibold text-[#0f1117]">{{ __('Create a Project') }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ __('Set up time tracking') }}</div>
                                    </a>
                                @else
                                    <a href="{{ route('settings.index') }}"
                                        class="p-4 rounded-xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors text-center">
                                        <div class="text-2xl mb-1">⚙️</div>
                                        <div class="text-sm font-semibold text-[#0f1117]">{{ __('Explore Settings') }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ __('Customize your account') }}</div>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="mt-6 flex justify-center">
                            <button wire:click="complete"
                                class="px-8 py-3 text-[#0f1117] rounded-xl text-sm font-bold"
                                style="background:#f59e0b;">
                                {{ __('Go to Dashboard →') }}
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
    </div>
</div>
