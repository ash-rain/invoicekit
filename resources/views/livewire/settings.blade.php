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
        'payments' => __('Payments'),
        'notifications' => __('Notifications'),
        'ai' => __('AI'),
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

            {{-- Language Preference --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Language Preference') }}</label>
                <p class="text-xs text-gray-400 mb-1.5">{{ __('Select your preferred app language') }}</p>
                <select wire:model="locale"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('locale') border-red-400 @enderror">
                    <option value="">— {{ __('System default') }} —</option>
                    @foreach ($supportedLanguages as $code)
                        @php $localeData = $localeNames[$code] ?? ['flag' => '', 'name' => strtoupper($code)]; @endphp
                        <option value="{{ $code }}">{{ $localeData['flag'] }} {{ $localeData['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('locale')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
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

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('Invoice Numbering Format') }}</label>
                    <p class="text-xs text-gray-400 mb-1.5">{{ __('Choose how invoice numbers are generated.') }}</p>
                    <select wire:model.live="invoiceNumberingFormat"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="standard">{{ __('Standard (PREFIX-YEAR-NUMBER)') }}</option>
                        <option value="bg_sequential">{{ __('Bulgarian (10-digit sequential)') }}</option>
                    </select>
                    @error('invoiceNumberingFormat')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @if ($invoiceNumberingFormat === 'standard')
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Invoice Number Prefix') }}</label>
                        <p class="text-xs text-gray-400 mb-1.5">
                            {{ __('Optional alphanumeric prefix added before the invoice number (e.g. INV, 2024).') }}
                        </p>
                        <input wire:model="invoicePrefix" type="text" placeholder="{{ __('e.g. INV') }}"
                            maxlength="20"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('invoicePrefix') border-red-400 @enderror" />
                        @error('invoicePrefix')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Invoice Starting Number') }}</label>
                        <p class="text-xs text-gray-400 mb-1.5">
                            {{ __('The first invoice sequence number for this company. Existing invoices are not renumbered.') }}
                        </p>
                        <input wire:model="invoiceStartingNumber" type="number" min="1" max="99999"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('invoiceStartingNumber') border-red-400 @enderror" />
                        @error('invoiceStartingNumber')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($invoiceNumberingFormat === 'bg_sequential')
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('Invoice Starting Number') }}</label>
                        <p class="text-xs text-gray-400 mb-1.5">
                            {{ __('The first 10-digit sequential number for Bulgarian compliance (ЗДДС Art. 114).') }}
                        </p>
                        <input wire:model="bgInvoiceSequenceStart" type="number" min="1"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('bgInvoiceSequenceStart') border-red-400 @enderror" />
                        @error('bgInvoiceSequenceStart')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('Issued By Default Name') }}</label>
                    <p class="text-xs text-gray-400 mb-1.5">
                        {{ __('Pre-fill the "Issued by" field on new invoices (required for Bulgarian compliance).') }}
                    </p>
                    <input wire:model="issuedByDefaultName" type="text"
                        placeholder="{{ __('Your name or position') }}" maxlength="255"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('issuedByDefaultName') border-red-400 @enderror" />
                    @error('issuedByDefaultName')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
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
                    @if ($invoiceLogoUpload && !$errors->has('invoiceLogoUpload'))
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

            {{-- Template picker card --}}
            <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ __('Default Invoice Template') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Choose the default PDF template for new invoices. You can override this per invoice.') }}
                    </p>
                </div>
                @php
                    $templates = app(\App\Services\InvoiceTemplateService::class)->getAvailableTemplates();
                @endphp
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($templates as $slug => $meta)
                        <label wire:key="tpl-{{ $slug }}"
                            class="relative flex flex-col gap-1 cursor-pointer rounded-xl border-2 p-3 transition-colors
                                {{ $invoiceTemplate === $slug ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="invoiceTemplate" value="{{ $slug }}"
                                class="sr-only">
                            <span class="text-sm font-semibold text-gray-900">{{ $meta['name'] }}</span>
                            <span class="text-xs text-gray-500 leading-snug">{{ $meta['description'] }}</span>
                            @if ($invoiceTemplate === $slug)
                                <span
                                    class="absolute top-2 right-2 w-4 h-4 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
                @error('invoiceTemplate')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
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

    {{-- ── PAYMENTS TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'payments'" x-cloak>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl text-sm">
                {{ session('warning') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Stripe Connect --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-5">
            <div>
                <h2 class="text-base font-semibold text-[#0f1117]" style="font-family:'Syne',sans-serif;">
                    {{ __('Online Payments via Stripe') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Connect your Stripe account so your clients can pay invoices online. Payments go directly to your bank account.') }}
                </p>
            </div>

            @if (auth()->user()->hasStripeConnect())
                {{-- Connected state --}}
                <div
                    class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-green-800">{{ __('Stripe Connected') }}</p>
                            <p class="text-xs text-green-600">
                                {{ __('Your account is active and ready to accept payments.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('stripe-connect.dashboard') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            {{ __('Stripe Dashboard') }}
                        </a>
                        <form method="POST" action="{{ route('stripe-connect.disconnect') }}"
                            onsubmit="return confirm('{{ __('Disconnect Stripe? Existing payment links will stop working.') }}')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                                {{ __('Disconnect') }}
                            </button>
                        </form>
                    </div>
                </div>
            @elseif (auth()->user()->stripe_connect_id)
                {{-- Account created but onboarding incomplete --}}
                <div
                    class="flex items-center justify-between rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-100">
                            <svg class="h-4 w-4 text-yellow-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M12 3C6.477 3 2 7.477 2 12s4.477 9 10 9 10-4.477 10-9S17.523 3 12 3z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">{{ __('Setup Incomplete') }}</p>
                            <p class="text-xs text-yellow-600">
                                {{ __('Finish your Stripe setup to start accepting payments.') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('stripe-connect.onboard') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] transition">
                            {{ __('Continue Setup') }}
                        </button>
                    </form>
                </div>
            @else
                {{-- Not connected state --}}
                <div
                    class="flex items-center justify-between rounded-xl border border-[#eaecf0] bg-[#fafafa] px-4 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ __('Not Connected') }}</p>
                            <p class="text-xs text-gray-500">
                                {{ __('Connect Stripe to let clients pay invoices online.') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('stripe-connect.onboard') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" />
                            </svg>
                            {{ __('Connect with Stripe') }}
                        </button>
                    </form>
                </div>
            @endif

            {{-- Platform fee note --}}
            @if (config('services.stripe.application_fee_percent') > 0)
                <p class="text-xs text-gray-400">
                    {{ __('A :percent% platform fee applies to online payments. Bank transfers are always free.', ['percent' => config('services.stripe.application_fee_percent')]) }}
                </p>
            @endif
        </div>

        {{-- Payment Methods --}}
        <div class="mt-6 bg-white rounded-2xl border border-[#eaecf0] p-6">
            <h2 class="text-base font-semibold text-[#0f1117] mb-1" style="font-family:'Syne',sans-serif;">
                {{ __('Payment Methods') }}
            </h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('Manage bank accounts and payment methods displayed on your invoices.') }}</p>
            @livewire('settings.payment-methods')
        </div>

    </div>

    {{-- ── NOTIFICATIONS TAB ────────────────────────────────────────────────── --}}
    <div x-show="tab === 'notifications'" x-cloak>

        @if (session('notifications_saved'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                {{ __('Notification preferences saved.') }}
            </div>
        @endif

        <form wire:submit="saveNotifications" class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-6">
            <div>
                <h3 class="text-sm font-bold text-gray-900">{{ __('Invoice Reminder Emails') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ __('Control when automated reminder emails are sent to clients about outstanding invoices.') }}
                </p>
            </div>

            <div>
                <label
                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reminder Before Due Date') }}</label>
                <p class="text-xs text-gray-400 mb-1.5">
                    {{ __('Send a reminder this many days before the invoice is due. Set to 0 to disable.') }}</p>
                <div class="flex items-center gap-3">
                    <input wire:model="reminderBeforeDueDays" type="number" min="0" max="30"
                        class="w-24 border border-gray-300 rounded-xl px-3 py-2.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('reminderBeforeDueDays') border-red-400 @enderror" />
                    <span class="text-sm text-gray-500">{{ __('days before due') }}</span>
                </div>
                @error('reminderBeforeDueDays')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" wire:model="reminderOnDueDay"
                        class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <span class="text-sm text-gray-700">{{ __('Send a reminder on the due date') }}</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Overdue Reminders') }}</label>
                <p class="text-xs text-gray-400 mb-2">
                    {{ __('Send follow-up reminders at these intervals after the due date (days overdue).') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ([7, 14, 21, 30] as $day)
                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="checkbox" value="{{ $day }}"
                                wire:model="reminderOverdueIntervals"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <span class="text-sm text-gray-600">+{{ $day }} {{ __('days') }}</span>
                        </label>
                    @endforeach
                </div>
                @error('reminderOverdueIntervals')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                    {{ __('Save Notification Preferences') }}
                </button>
            </div>
        </form>
    </div>

    {{-- ── AI TAB ───────────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'ai'" x-cloak>

        @if (session('ai_saved'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                {{ __('API key configured') }}
            </div>
        @endif

        @if (session('ai_key_removed'))
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-sm">
                {{ __('Gemini API key removed.') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-6">
            <div>
                <h3 class="text-sm font-bold text-gray-900">{{ __('Gemini API Key') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ __('Provide your own Gemini API key to bypass all app import limits and use your own Google AI quota.') }}
                </p>
            </div>

            {{-- Current key status --}}
            @if ($user->gemini_api_key)
                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl border border-green-200">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-green-800">{{ __('API key configured') }}</p>
                        <p class="text-xs text-green-600 mt-0.5">{{ __('No API key set') }}</p>
                    </div>
                    <button wire:click="removeGeminiKey"
                        wire:confirm="{{ __('Remove your Gemini API key? You will fall back to plan limits.') }}"
                        class="px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                        {{ __('Remove API key') }}
                    </button>
                </div>
            @else
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <p class="text-sm text-gray-500">{{ __('No API key set') }}</p>
                </div>
            @endif

            {{-- Add / replace key form --}}
            <form wire:submit="saveAi" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $user->gemini_api_key ? __('Replace API Key') : __('Add API Key') }}
                    </label>
                    <input wire:model="geminiApiKey" type="password" autocomplete="off" placeholder="AIza..."
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('geminiApiKey') border-red-400 @enderror" />
                    @error('geminiApiKey')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Instructions --}}
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-xs text-indigo-800 space-y-1.5">
                    <p class="font-semibold">{{ __('How to get a free Gemini API key:') }}</p>
                    <ol class="list-decimal list-inside space-y-1 text-indigo-700">
                        <li>{{ __('Go to') }} <a href="https://aistudio.google.com/app/apikey" target="_blank"
                                rel="noopener noreferrer"
                                class="underline font-medium">aistudio.google.com/app/apikey</a></li>
                        <li>{{ __('Click "Create API key" and copy it') }}</li>
                        <li>{{ __('Paste it above and click Save') }}</li>
                    </ol>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-5 py-2.5 bg-[#0f1117] text-white text-sm font-bold rounded-xl hover:bg-[#1a1f2e]">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
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
