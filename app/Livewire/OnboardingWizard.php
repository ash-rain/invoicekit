<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class OnboardingWizard extends Component
{
    public int $step = 1;

    // Step 1 — Company info (stored in user profile)
    public string $companyName = '';

    public string $companyCountry = 'BG';

    // Step 2 — First client
    public string $clientName = '';

    public string $clientEmail = '';

    public string $clientCountry = 'DE';

    public string $clientCurrency = 'EUR';

    // Step 3 — First invoice (summary, create later)
    public string $projectName = '';

    public string $hourlyRate = '';

    public bool $skipInvoice = false;

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
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateStep1();
            $this->step = 2;
        } elseif ($this->step === 2) {
            $this->validateStep2();
            $this->step = 3;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
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
        $this->validate([
            'clientName' => ['required', 'string', 'max:255'],
            'clientEmail' => ['nullable', 'email', 'max:255'],
            'clientCountry' => ['required', 'string', 'size:2'],
            'clientCurrency' => ['required', 'string', 'in:' . implode(',', self::CURRENCIES)],
        ]);
    }

    public function complete(): void
    {
        $this->validateStep1();
        $this->validateStep2();

        $this->validate([
            'projectName' => ['required_unless:skipInvoice,true', 'nullable', 'string', 'max:255'],
            'hourlyRate' => ['required_unless:skipInvoice,true', 'nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () {
            $user = Auth::user();
            $user->update(['name' => $this->companyName, 'onboarding_completed' => true]);

            // Create first client
            $client = Client::create([
                'user_id' => $user->id,
                'name' => $this->clientName,
                'email' => $this->clientEmail ?: null,
                'address' => null,
                'country' => $this->clientCountry,
                'currency' => $this->clientCurrency,
            ]);

            // Optionally create first project
            if (! $this->skipInvoice && $this->projectName) {
                Project::create([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'name' => $this->projectName,
                    'hourly_rate' => (float) $this->hourlyRate,
                    'currency' => $this->clientCurrency,
                    'status' => 'active',
                ]);
            }
        });

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.onboarding-wizard', [
            'countries' => self::COUNTRIES,
            'currencies' => self::CURRENCIES,
        ]);
    }
}
