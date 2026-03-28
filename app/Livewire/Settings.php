<?php

namespace App\Livewire;

use App\Models\Company;
use App\Services\VatExemptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Settings extends Component
{
    use WithFileUploads;

    public string $activeTab = 'profile';

    // ----- Profile tab -----
    public string $name = '';

    public string $displayName = '';

    public string $tagline = '';

    public string $website = '';

    public string $phone = '';

    public $profilePhotoUpload = null;

    // ----- Business tab -----
    public string $companyName = '';

    public string $addressLine1 = '';

    public string $addressLine2 = '';

    public string $city = '';

    public string $postalCode = '';

    public string $companyCountry = '';

    public string $vatNumber = '';

    public string $registrationNumber = '';

    public string $bankName = '';

    public string $bankIban = '';

    public string $bankBic = '';

    // ----- Invoicing tab -----
    public string $defaultCurrency = 'EUR';

    public int $defaultPaymentTerms = 30;

    public string $defaultInvoiceNotes = '';

    public $invoiceLogoUpload = null;

    public bool $vatExempt = false;

    public string $vatExemptReason = '';

    public string $vatExemptNoticeLanguage = 'local';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->displayName = $user->display_name ?? '';
        $this->tagline = $user->tagline ?? '';
        $this->website = $user->website ?? '';
        $this->phone = $user->phone ?? '';

        $company = $user->currentCompany;

        if ($company) {
            $this->companyName = $company->name ?? '';
            $this->addressLine1 = $company->address_line1 ?? '';
            $this->addressLine2 = $company->address_line2 ?? '';
            $this->city = $company->city ?? '';
            $this->postalCode = $company->postal_code ?? '';
            $this->companyCountry = $company->country ?? '';
            $this->vatNumber = $company->vat_number ?? '';
            $this->registrationNumber = $company->registration_number ?? '';
            $this->bankName = $company->bank_name ?? '';
            $this->bankIban = $company->bank_iban ?? '';
            $this->bankBic = $company->bank_bic ?? '';
            $this->defaultCurrency = $company->default_currency ?? 'EUR';
            $this->defaultPaymentTerms = $company->default_payment_terms ?? 30;
            $this->defaultInvoiceNotes = $company->default_invoice_notes ?? '';
            $this->vatExempt = (bool) ($company->vat_exempt ?? false);
            $this->vatExemptReason = $company->vat_exempt_reason ?? '';
            $this->vatExemptNoticeLanguage = $company->vat_exempt_notice_language ?? 'local';
        }
    }

    public function updatedProfilePhotoUpload(): void
    {
        $this->validateOnly('profilePhotoUpload');
    }

    public function updatedInvoiceLogoUpload(): void
    {
        $this->validateOnly('invoiceLogoUpload');
    }

    public function saveProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'displayName' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'profilePhotoUpload' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'name' => $this->name,
            'display_name' => $this->displayName ?: null,
            'tagline' => $this->tagline ?: null,
            'website' => $this->website ?: null,
            'phone' => $this->phone ?: null,
        ];

        if ($this->profilePhotoUpload) {
            if ($user->profile_photo) {
                Storage::disk('minio')->delete($user->profile_photo);
            }

            $path = $this->profilePhotoUpload->store('profile-photos', 'minio');
            $data['profile_photo'] = $path;
            $this->profilePhotoUpload = null;
        }

        $user->update($data);

        session()->flash('profile_saved', true);
    }

    public function saveBusiness(): void
    {
        $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'addressLine1' => ['nullable', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postalCode' => ['nullable', 'string', 'max:20'],
            'companyCountry' => ['required', 'string', 'size:2'],
            'vatNumber' => ['nullable', 'string', 'max:50'],
            'registrationNumber' => ['nullable', 'string', 'max:50'],
            'bankName' => ['nullable', 'string', 'max:100'],
            'bankIban' => ['nullable', 'string', 'max:50'],
            'bankBic' => ['nullable', 'string', 'max:11'],
        ]);

        $user = Auth::user();
        $company = $user->currentCompany;

        $data = [
            'name' => $this->companyName,
            'address_line1' => $this->addressLine1 ?: null,
            'address_line2' => $this->addressLine2 ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postalCode ?: null,
            'country' => $this->companyCountry,
            'vat_number' => $this->vatNumber ?: null,
            'registration_number' => $this->registrationNumber ?: null,
            'bank_name' => $this->bankName ?: null,
            'bank_iban' => $this->bankIban ?: null,
            'bank_bic' => $this->bankBic ?: null,
        ];

        if ($company) {
            $company->update($data);
        } else {
            $company = Company::create(array_merge($data, ['user_id' => $user->id]));
            $user->update(['current_company_id' => $company->id]);
        }

        session()->flash('business_saved', true);
    }

    public function saveInvoicing(): void
    {
        $this->validate([
            'defaultCurrency' => ['required', 'string', 'max:3'],
            'defaultPaymentTerms' => ['required', 'integer', 'min:0', 'max:365'],
            'defaultInvoiceNotes' => ['nullable', 'string', 'max:2000'],
            'invoiceLogoUpload' => ['nullable', 'image', 'max:2048'],
            'vatExempt' => ['boolean'],
            'vatExemptReason' => ['nullable', 'string', 'max:500'],
            'vatExemptNoticeLanguage' => ['required', 'string', 'in:local,en'],
        ]);

        $user = Auth::user();
        $company = $user->currentCompany;

        if (! $company) {
            session()->flash('error', __('Please save your business details first.'));

            return;
        }

        $data = [
            'default_currency' => $this->defaultCurrency,
            'default_payment_terms' => $this->defaultPaymentTerms,
            'default_invoice_notes' => $this->defaultInvoiceNotes ?: null,
            'vat_exempt' => $this->vatExempt,
            'vat_exempt_reason' => $this->vatExemptReason ?: null,
            'vat_exempt_notice_language' => $this->vatExemptNoticeLanguage,
        ];

        if ($this->invoiceLogoUpload) {
            if ($company->invoice_logo) {
                Storage::disk('minio')->delete($company->invoice_logo);
            }

            $path = $this->invoiceLogoUpload->store('invoice-logos', 'minio');
            $data['invoice_logo'] = $path;
            $this->invoiceLogoUpload = null;
        }

        $company->update($data);

        session()->flash('invoicing_saved', true);
    }

    public function getVatExemptionInfoProperty(): ?array
    {
        if (! $this->companyCountry) {
            return null;
        }

        return app(VatExemptionService::class)->getExemptionForCountry($this->companyCountry);
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.settings', [
            'user' => $user,
            'company' => $user->currentCompany,
            'vatExemptionInfo' => $this->vatExemptionInfo,
        ]);
    }
}
