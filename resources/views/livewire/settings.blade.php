<div x-data="{ tab: @entangle('activeTab') }" class="p-6 max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
            {{ __('Settings') }}
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage your profile, business, and invoicing preferences') }}</p>
    </div>

    {{-- Tab nav --}}
    <div class="flex gap-1 border-b border-[#eaecf0] mb-6 overflow-x-auto">
        @foreach ([
        'profile' => __('Profile'),
        'business' => __('Business'),
        'invoicing' => __('Invoicing'),
        'account' => __('Account'),
    ] as $key => $label)
            <button type="button" x-on:click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}'
                    ?
                    'border-b-2 border-[#0f1117] text-[#0f1117] font-semibold' :
                    'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2.5 text-sm whitespace-nowrap transition-colors">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ── PROFILE TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'profile'" x-cloak>

        @if (session('profile_saved'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                {{ __('Profile saved.') }}
            </div>
        @endif

        <form wire:submit="saveProfile" class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-5">

            {{-- Photo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Profile Photo') }}</label>
                <div class="flex items-center gap-4">
                    @if ($user->profilePhotoUrl())
                        <img src="{{ $user->profilePhotoUrl() }}" alt="Profile"
                            class="w-14 h-14 rounded-full object-cover border border-gray-200">
                    @else
                        <div
                            class="w-14 h-14 rounded-full bg-[#0f1117] flex items-center justify-center text-white text-xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        @if ($profilePhotoUpload && ! $errors->has('profilePhotoUpload'))
                            <img src="{{ $profilePhotoUpload->temporaryUrl() }}" alt="Preview"
                                class="w-14 h-14 rounded-full object-cover border border-gray-200 mb-2">
                        @endif
                        <label
                            class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span wire:loading.remove wire:target="profilePhotoUpload">{{ __('Choose photo') }}</span>
                            <span wire:loading wire:target="profilePhotoUpload"
                                class="text-gray-500">{{ __('Uploading…') }}</span>
                            <input wire:model="profilePhotoUpload" type="file" accept="image/*" class="sr-only" />
                        </label>
                        <p class="text-xs text-gray-400 mt-1">{{ __('JPG, PNG or GIF — max 2MB') }}</p>
                    </div>
                </div>
                @error('profilePhotoUpload')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Full Name') }} <span
                        class="text-red-500">*</span></label>
                <input wire:model="name" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror" />
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Display name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Display Name') }}</label>
                <input wire:model="displayName" type="text" placeholder="{{ __('How you appear on invoices') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            {{-- Tagline --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tagline') }}</label>
                <input wire:model="tagline" type="text" placeholder="{{ __('e.g. Freelance Web Developer') }}"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            {{-- Website --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Website') }}</label>
                <input wire:model="website" type="url" placeholder="https://yoursite.com"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('website') border-red-400 @enderror" />
                @error('website')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                <input wire:model="phone" type="text" placeholder="+1 555 000 0000"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                    {{ __('Save Profile') }}
                </button>
            </div>
        </form>
    </div>

    {{-- ── BUSINESS TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'business'" x-cloak>

        @if (session('business_saved'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                {{ __('Business details saved.') }}
            </div>
        @endif

        <form wire:submit="saveBusiness" class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company / Business Name') }} <span
                        class="text-red-500">*</span></label>
                <input wire:model="companyName" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('companyName') border-red-400 @enderror" />
                @error('companyName')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address Line 1') }}</label>
                <input wire:model="addressLine1" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address Line 2') }}</label>
                <input wire:model="addressLine2" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('City') }}</label>
                    <input wire:model="city" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Postal Code') }}</label>
                    <input wire:model="postalCode" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Country') }} <span
                        class="text-red-500">*</span></label>
                <select wire:model.live="companyCountry"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('companyCountry') border-red-400 @enderror">
                    <option value="">{{ __('— Select country —') }}</option>
                    @foreach (\App\Livewire\OnboardingWizard::COUNTRIES as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('companyCountry')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-[#eaecf0]">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('VAT Number') }}</label>
                    <input wire:model="vatNumber" type="text" placeholder="DE123456789"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Registration Number') }}</label>
                    <input wire:model="registrationNumber" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>

            <hr class="border-[#eaecf0]">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bank Name') }}</label>
                <input wire:model="bankName" type="text"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('IBAN') }}</label>
                    <input wire:model="bankIban" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('BIC / SWIFT') }}</label>
                    <input wire:model="bankBic" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                    {{ __('Save Business Details') }}
                </button>
            </div>
        </form>
    </div>

    {{-- ── INVOICING TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'invoicing'" x-cloak>

        @if (session('invoicing_saved'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                {{ __('Invoicing settings saved.') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="saveInvoicing" class="space-y-5">

            {{-- Defaults card --}}
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-5">
                <h3 class="text-sm font-bold text-gray-900">{{ __('Invoice Defaults') }}</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Default Currency') }}</label>
                        <select wire:model="defaultCurrency"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach (['EUR', 'USD', 'BGN', 'RON', 'PLN', 'CZK', 'HUF'] as $cur)
                                <option value="{{ $cur }}">{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment Terms (days)') }}</label>
                        <input wire:model="defaultPaymentTerms" type="number" min="0" max="365"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('defaultPaymentTerms') border-red-400 @enderror" />
                        @error('defaultPaymentTerms')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('Default Invoice Notes') }}</label>
                    <textarea wire:model="defaultInvoiceNotes" rows="3"
                        placeholder="{{ __('Bank details, payment instructions…') }}"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Logo upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Invoice Logo') }}</label>
                    @if ($company?->logoUrl())
                        <div class="mb-3">
                            <img src="{{ $company->logoUrl() }}" alt="Logo"
                                class="h-12 object-contain border border-gray-200 rounded-lg p-1">
                        </div>
                    @endif
                    @if ($invoiceLogoUpload && ! $errors->has('invoiceLogoUpload'))
                        <div class="mb-2">
                            <img src="{{ $invoiceLogoUpload->temporaryUrl() }}" alt="Preview"
                                class="h-12 object-contain border border-gray-200 rounded-lg p-1">
                        </div>
                    @endif
                    <label
                        class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span wire:loading.remove wire:target="invoiceLogoUpload">{{ __('Choose logo') }}</span>
                        <span wire:loading wire:target="invoiceLogoUpload"
                            class="text-gray-500">{{ __('Uploading…') }}</span>
                        <input wire:model="invoiceLogoUpload" type="file" accept="image/*" class="sr-only" />
                    </label>
                    <p class="text-xs text-gray-400 mt-1">{{ __('PNG or SVG recommended — max 2MB') }}</p>
                    @error('invoiceLogoUpload')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- VAT Exemption card --}}
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ __('Small-Business VAT Exemption') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Enable if your business qualifies for a national small-business VAT exemption (e.g. Kleinunternehmerregelung in Germany or similar schemes in other EU countries).') }}
                    </p>
                </div>

                @if ($vatExemptionInfo && !($vatExemptionInfo['available'] ?? true))
                    <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        {{ $vatExemptionInfo['unavailable_reason'] ?? __('VAT exemption is not available for your country.') }}
                    </div>
                @endif

                @if ($vatExemptionInfo && ($vatExemptionInfo['available'] ?? false))
                    <div class="p-3 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl text-sm space-y-1">
                        <p><strong>{{ __('Legal basis:') }}</strong> {{ $vatExemptionInfo['legal_basis'] ?? '' }}</p>
                        <p><strong>{{ __('Threshold:') }}</strong>
                            {{ $vatExemptionInfo['threshold_amount'] ?? '' }}
                            {{ $vatExemptionInfo['threshold_currency'] ?? '' }}
                            @if (isset($vatExemptionInfo['threshold_eur_approx']))
                                (≈ €{{ number_format($vatExemptionInfo['threshold_eur_approx']) }})
                            @endif
                        </p>
                    </div>
                @endif

                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="vatExempt"
                        class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        @if ($vatExemptionInfo && !($vatExemptionInfo['available'] ?? true)) disabled @endif />
                    <span class="text-sm text-gray-700">
                        {{ __('I qualify for the small-business VAT exemption in my country') }}
                    </span>
                </label>

                @if ($vatExempt)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Exemption Basis / Note') }}
                            <span class="text-gray-400 font-normal">({{ __('optional, for your records') }})</span>
                        </label>
                        <input wire:model="vatExemptReason" type="text"
                            placeholder="{{ __('e.g. Annual revenue below threshold per §19 UStG') }}"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Invoice Notice Language') }}</label>
                        <select wire:model="vatExemptNoticeLanguage"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="local">{{ __('Local language (per country config)') }}</option>
                            <option value="en">{{ __('English') }}</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ __('This text will appear at the bottom of your invoices.') }}
                        </p>
                        @if ($vatExemptionInfo)
                            <div
                                class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 italic">
                                "{{ $vatExemptNoticeLanguage === 'en'
                                    ? $vatExemptionInfo['invoice_notice_en'] ?? ''
                                    : $vatExemptionInfo['invoice_notice_local'] ?? '' }}"
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                    {{ __('Save Invoicing Settings') }}
                </button>
            </div>
        </form>
    </div>

    {{-- ── ACCOUNT TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'account'" x-cloak>

        <div class="space-y-6">

            {{-- Update password --}}
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6">
                @include('profile.partials.update-password-form')
            </div>

            {{-- Delete account --}}
            <div class="bg-white rounded-2xl border border-red-100 p-6">
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>

</div>
