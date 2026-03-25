<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateEditClient extends Component
{
    public ?Client $client = null;

    public string $name = '';
    public string $email = '';
    public string $address = '';
    public string $country = 'BG';
    public string $vat_number = '';
    public string $currency = 'EUR';

    public const CURRENCIES = ['EUR', 'USD', 'BGN', 'RON', 'PLN', 'CZK', 'HUF'];

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
        'JP' => 'Japan',
        'CN' => 'China',
        'IN' => 'India',
        'BR' => 'Brazil',
        'ZA' => 'South Africa',
        'SG' => 'Singapore',
        'AE' => 'United Arab Emirates',
    ];

    /** VAT number format patterns per EU country prefix. */
    private const VAT_PATTERNS = [
        'AT' => '/^ATU\d{8}$/',
        'BE' => '/^BE0\d{9}$/',
        'BG' => '/^BG\d{9,10}$/',
        'HR' => '/^HR\d{11}$/',
        'CY' => '/^CY\d{8}[A-Z]$/',
        'CZ' => '/^CZ\d{8,10}$/',
        'DK' => '/^DK\d{8}$/',
        'EE' => '/^EE\d{9}$/',
        'FI' => '/^FI\d{8}$/',
        'FR' => '/^FR[A-Z0-9]{2}\d{9}$/',
        'DE' => '/^DE\d{9}$/',
        'GR' => '/^EL\d{9}$/',
        'HU' => '/^HU\d{8}$/',
        'IE' => '/^IE\d[A-Z0-9]\d{5}[A-Z]{1,2}$/',
        'IT' => '/^IT\d{11}$/',
        'LV' => '/^LV\d{11}$/',
        'LT' => '/^LT(\d{9}|\d{12})$/',
        'LU' => '/^LU\d{8}$/',
        'MT' => '/^MT\d{8}$/',
        'NL' => '/^NL\d{9}B\d{2}$/',
        'PL' => '/^PL\d{10}$/',
        'PT' => '/^PT\d{9}$/',
        'RO' => '/^RO\d{2,10}$/',
        'SK' => '/^SK\d{10}$/',
        'SI' => '/^SI\d{8}$/',
        'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/',
        'SE' => '/^SE\d{12}$/',
    ];

    public function mount(?Client $client = null): void
    {
        if ($client && $client->exists) {
            $this->authorize('update', $client);
            $this->client = $client;
            $this->name = $client->name;
            $this->email = $client->email ?? '';
            $this->address = $client->address ?? '';
            $this->country = $client->country;
            $this->vat_number = $client->vat_number ?? '';
            $this->currency = $client->currency;
        }
    }

    protected function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'address'    => ['nullable', 'string', 'max:1000'],
            'country'    => ['required', 'string', 'size:2'],
            'vat_number' => ['nullable', 'string', 'max:30'],
            'currency'   => ['required', 'string', 'in:' . implode(',', self::CURRENCIES)],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // EU VAT number format check
        if (!empty($validated['vat_number'])) {
            $vatNumber = strtoupper(trim($validated['vat_number']));
            $pattern = self::VAT_PATTERNS[$validated['country']] ?? null;
            if ($pattern && !preg_match($pattern, $vatNumber)) {
                $this->addError('vat_number', 'Invalid VAT number format for ' . ($this::COUNTRIES[$validated['country']] ?? $validated['country']) . '.');
                return;
            }
            $validated['vat_number'] = $vatNumber;
        }

        $data = array_merge($validated, ['user_id' => Auth::id()]);

        if ($this->client && $this->client->exists) {
            $this->client->update($data);
            session()->flash('success', 'Client updated successfully.');
        } else {
            Client::create($data);
            session()->flash('success', 'Client created successfully.');
        }

        $this->redirect(route('clients.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.clients.create-edit-client', [
            'countries'  => self::COUNTRIES,
            'currencies' => self::CURRENCIES,
        ]);
    }
}
