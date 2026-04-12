<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Services\EuVatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.fullscreen')]
class OnboardingWizard extends Component
{
    public int $step = 1;

    // Step 1 — Company info
    public string $companyName = '';

    public string $companyCountry = 'BG';

    public string $companyAddress = '';

    public string $companyPhone = '';

    // Step 2 — VAT & Tax
    public string $vatNumber = '';

    public string $registrationNumber = '';

    public bool $vatExempt = false;

    // Step 3 — First client
    public string $clientName = '';

    public string $clientEmail = '';

    public string $clientCountry = 'BG';

    public string $clientCurrency = 'BGN';

    public string $clientVatNumber = '';

    // Step 4 — First project
    public string $projectName = '';

    public string $hourlyRate = '';

    public bool $skipProject = false;

    // Step 5 — Payment method
    public string $paymentMethodType = 'bank_transfer';

    public string $bankIban = '';

    public string $bankBic = '';

    public bool $skipPayment = false;

    public const COUNTRIES = [
        'AT' => 'Austria',
        'BE' => 'Belgium',
        'BG' => 'Bulgaria',
        'HR' => 'Croatia',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'EE' => 'Estonia',
        'FI' => 'Finland',
        'FR' => 'France',
        'DE' => 'Germany',
        'GR' => 'Greece',
        'HU' => 'Hungary',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'LV' => 'Latvia',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MT' => 'Malta',
        'NL' => 'Netherlands',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'RO' => 'Romania',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'ES' => 'Spain',
        'SE' => 'Sweden',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'CH' => 'Switzerland',
        'NO' => 'Norway',
        'AU' => 'Australia',
        'CA' => 'Canada',
    ];

    public const CURRENCIES = ['EUR', 'USD', 'BGN', 'RON', 'PLN', 'CZK', 'HUF'];

    public function mount(): void
    {
        if (Auth::user()->onboarding_completed) {
            $this->redirect(route('dashboard'));
        }

        $this->companyName = Auth::user()->name;
        $this->syncClientCountryToCompany();
    }

    /** When company country changes, update client country and currency defaults. */
    public function updatedCompanyCountry(): void
    {
        $this->syncClientCountryToCompany();
    }

    /** When client country changes, update currency from country defaults. */
    public function updatedClientCountry(): void
    {
        $defaults = config('country_defaults.'.$this->clientCountry, []);
        if (isset($defaults['currency']) && in_array($defaults['currency'], self::CURRENCIES, true)) {
            $this->clientCurrency = $defaults['currency'];
        }
    }

    #[Computed]
    public function isEuCountry(): bool
    {
        return app(EuVatService::class)->isEuCountry($this->companyCountry);
    }

    #[Computed]
    public function vatNumberHint(): string
    {
        $hints = [
            'AT' => 'ATU followed by 8 digits (ATU12345678)',
            'BE' => 'BE followed by 10 digits (BE0123456789)',
            'BG' => 'BG followed by 9 or 10 digits (BG123456789)',
            'HR' => 'HR followed by 11 digits (HR12345678901)',
            'CY' => 'CY followed by 8 digits and a letter (CY12345678L)',
            'CZ' => 'CZ followed by 8-10 digits (CZ12345678)',
            'DK' => 'DK followed by 8 digits (DK12345678)',
            'EE' => 'EE followed by 9 digits (EE123456789)',
            'FI' => 'FI followed by 8 digits (FI12345678)',
            'FR' => 'FR followed by 2 chars and 9 digits (FRXX123456789)',
            'DE' => 'DE followed by 9 digits (DE123456789)',
            'GR' => 'EL followed by 9 digits (EL123456789)',
            'HU' => 'HU followed by 8 digits (HU12345678)',
            'IE' => 'IE followed by 8-9 chars (IE1234567X)',
            'IT' => 'IT followed by 11 digits (IT12345678901)',
            'LV' => 'LV followed by 11 digits (LV12345678901)',
            'LT' => 'LT followed by 9 or 12 digits (LT123456789)',
            'LU' => 'LU followed by 8 digits (LU12345678)',
            'MT' => 'MT followed by 8 digits (MT12345678)',
            'NL' => 'NL followed by 9 digits and B and 2 digits (NL123456789B01)',
            'PL' => 'PL followed by 10 digits (PL1234567890)',
            'PT' => 'PT followed by 9 digits (PT123456789)',
            'RO' => 'RO followed by 2-10 digits (RO12345678)',
            'SK' => 'SK followed by 10 digits (SK1234567890)',
            'SI' => 'SI followed by 8 digits (SI12345678)',
            'ES' => 'ES followed by a letter, 7 digits, and a letter/digit (ESX1234567Y)',
            'SE' => 'SE followed by 12 digits (SE123456789012)',
        ];

        return $hints[$this->companyCountry] ?? '';
    }

    #[Computed]
    public function registrationNumberLabel(): string
    {
        return config('country_defaults.'.$this->companyCountry.'.registration_number_label', __('Registration Number'));
    }

    #[Computed]
    public function registrationNumberHint(): string
    {
        return config('country_defaults.'.$this->companyCountry.'.registration_number_hint', '');
    }

    #[Computed]
    public function vatExemptThreshold(): string
    {
        if (! $this->isEuCountry) {
            return '';
        }

        $exemption = config('vat_exemptions.'.$this->companyCountry);
        if (! $exemption || ! ($exemption['available'] ?? false)) {
            return '';
        }

        $amount = number_format($exemption['threshold_amount'] ?? 0);
        $currency = $exemption['threshold_currency'] ?? '';
        $eur = $exemption['threshold_eur_approx'] ?? 0;

        return "{$amount} {$currency} (~€{$eur})";
    }

    #[Computed]
    public function ibanHint(): string
    {
        $lengths = [
            'AT' => 20, 'BE' => 16, 'BG' => 22, 'HR' => 21, 'CY' => 28,
            'CZ' => 24, 'DK' => 18, 'EE' => 20, 'FI' => 18, 'FR' => 27,
            'DE' => 22, 'GR' => 27, 'HU' => 28, 'IE' => 22, 'IT' => 27,
            'LV' => 21, 'LT' => 20, 'LU' => 20, 'MT' => 31, 'NL' => 18,
            'PL' => 28, 'PT' => 25, 'RO' => 24, 'SK' => 24, 'SI' => 19,
            'ES' => 24, 'SE' => 24, 'GB' => 22, 'NO' => 15,
        ];

        $len = $lengths[$this->companyCountry] ?? null;

        return $len ? "{$this->companyCountry} + ".($len - 2).' characters ('.$len.' total)' : '';
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateStep1();
            $this->step = 2;
        } elseif ($this->step === 2) {
            $this->validateStep2();
            $this->step = 3;
        } elseif ($this->step === 3) {
            $this->validateStep3();
            $this->step = 4;
        } elseif ($this->step === 4) {
            if (! $this->skipProject) {
                $this->validateStep4();
            }
            $this->step = 5;
        } elseif ($this->step === 5) {
            if (! $this->skipPayment) {
                $this->validateStep5();
            }
            $this->step = 6;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function complete(): void
    {
        $this->validateStep1();
        $this->validateStep2();
        $this->validateStep3();

        if (! $this->skipProject) {
            $this->validateStep4();
        }

        if (! $this->skipPayment) {
            $this->validateStep5();
        }

        DB::transaction(function () {
            $user = Auth::user();
            $user->update(['name' => $this->companyName, 'onboarding_completed' => true, 'phone' => $this->companyPhone ?: null]);

            if (! $user->currentCompany) {
                $countryDefaults = config('country_defaults.'.$this->companyCountry, []);

                $company = Company::create([
                    'user_id' => $user->id,
                    'name' => $this->companyName,
                    'country' => $this->companyCountry,
                    'address_line1' => $this->companyAddress ?: null,
                    'vat_number' => $this->vatNumber ?: null,
                    'registration_number' => $this->registrationNumber ?: null,
                    'vat_exempt' => $this->vatExempt,
                    'default_currency' => $countryDefaults['currency'] ?? $this->clientCurrency,
                    'invoice_numbering_format' => $countryDefaults['invoice_numbering_format'] ?? 'standard',
                    'issued_by_default_name' => $this->vatExempt ? $user->name : null,
                ]);

                $user->update(['current_company_id' => $company->id]);
            } else {
                $company = $user->currentCompany;
                $company->update([
                    'vat_number' => $this->vatNumber ?: null,
                    'registration_number' => $this->registrationNumber ?: null,
                    'vat_exempt' => $this->vatExempt,
                ]);
            }

            $client = Client::create([
                'user_id' => $user->id,
                'name' => $this->clientName,
                'email' => $this->clientEmail ?: null,
                'country' => $this->clientCountry,
                'currency' => $this->clientCurrency,
                'vat_number' => $this->clientVatNumber ?: null,
            ]);

            if (! $this->skipProject && $this->projectName) {
                Project::create([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'name' => $this->projectName,
                    'hourly_rate' => (float) $this->hourlyRate,
                    'currency' => $this->clientCurrency,
                    'status' => 'active',
                ]);
            }

            if (! $this->skipPayment) {
                $company->paymentMethods()->where('is_default', true)->update(['is_default' => false]);

                $pm = [
                    'company_id' => $company->id,
                    'type' => $this->paymentMethodType,
                    'is_default' => true,
                ];

                if ($this->paymentMethodType === PaymentMethod::TYPE_BANK_TRANSFER) {
                    $pm['bank_iban'] = $this->bankIban;
                    $pm['bank_bic'] = $this->bankBic ?: null;
                }

                PaymentMethod::create($pm);
            }
        });

        $this->redirect(route('dashboard'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.onboarding-wizard', [
            'countries' => self::COUNTRIES,
            'currencies' => self::CURRENCIES,
        ]);
    }

    private function syncClientCountryToCompany(): void
    {
        $this->clientCountry = $this->companyCountry;
        $defaults = config('country_defaults.'.$this->companyCountry, []);
        if (isset($defaults['currency']) && in_array($defaults['currency'], self::CURRENCIES, true)) {
            $this->clientCurrency = $defaults['currency'];
        }
    }

    private function validateStep1(): void
    {
        $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'companyCountry' => ['required', 'string', 'size:2'],
        ]);
    }

    private function validateStep2(): void
    {
        $rules = [
            'vatNumber' => ['nullable', 'string', 'max:20'],
            'registrationNumber' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->isEuCountry && ! $this->vatExempt) {
            $rules['vatNumber'] = ['required', 'string', 'max:20'];
        }

        $this->validate($rules);
    }

    private function validateStep3(): void
    {
        $this->validate([
            'clientName' => ['required', 'string', 'max:255'],
            'clientEmail' => ['nullable', 'email', 'max:255'],
            'clientCountry' => ['required', 'string', 'size:2'],
            'clientCurrency' => ['required', 'string', 'in:'.implode(',', self::CURRENCIES)],
            'clientVatNumber' => ['nullable', 'string', 'max:20'],
        ]);
    }

    private function validateStep4(): void
    {
        $this->validate([
            'projectName' => ['required', 'string', 'max:255'],
            'hourlyRate' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function validateStep5(): void
    {
        $rules = [
            'paymentMethodType' => ['required', 'in:bank_transfer,cash'],
        ];

        if ($this->paymentMethodType === PaymentMethod::TYPE_BANK_TRANSFER) {
            $rules['bankIban'] = ['required', 'string', 'max:34'];
            $rules['bankBic'] = ['nullable', 'string', 'max:11'];
        }

        $this->validate($rules);
    }
}
