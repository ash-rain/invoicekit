<?php

namespace App\Livewire\Settings;

use App\Models\PaymentMethod;
use App\Services\PlanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PaymentMethods extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $type = PaymentMethod::TYPE_BANK_TRANSFER;

    public string $label = '';

    public string $bankName = '';

    public string $bankIban = '';

    public string $bankBic = '';

    public string $notes = '';

    public bool $confirmingDelete = false;

    public ?int $deletingId = null;

    public function add(): void
    {
        $planService = app(PlanService::class);
        $user = Auth::user();

        if (! $planService->canAddPaymentMethod($user)) {
            session()->flash('payment_method_error', __('You have reached the payment method limit for your plan. Upgrade to add more payment methods.'));

            return;
        }

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $method = $this->findMethod($id);
        if (! $method) {
            return;
        }

        if ($method->type === PaymentMethod::TYPE_STRIPE) {
            return;
        }

        $this->editingId = $method->id;
        $this->type = $method->type;
        $this->label = $method->label ?? '';
        $this->bankName = $method->bank_name ?? '';
        $this->bankIban = $method->bank_iban ?? '';
        $this->bankBic = $method->bank_bic ?? '';
        $this->notes = $method->notes ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'type' => ['required', 'string', 'in:'.implode(',', [PaymentMethod::TYPE_BANK_TRANSFER, PaymentMethod::TYPE_CASH])],
            'label' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->type === PaymentMethod::TYPE_BANK_TRANSFER) {
            $rules['bankName'] = ['nullable', 'string', 'max:100'];
            $rules['bankIban'] = ['required', 'string', 'max:50'];
            $rules['bankBic'] = ['nullable', 'string', 'max:11'];
        }

        $this->validate($rules);

        $company = Auth::user()->currentCompany;
        if (! $company) {
            return;
        }

        $data = [
            'company_id' => $company->id,
            'type' => $this->type,
            'label' => $this->label ?: null,
            'notes' => $this->notes ?: null,
            'bank_name' => $this->type === PaymentMethod::TYPE_BANK_TRANSFER ? ($this->bankName ?: null) : null,
            'bank_iban' => $this->type === PaymentMethod::TYPE_BANK_TRANSFER ? $this->bankIban : null,
            'bank_bic' => $this->type === PaymentMethod::TYPE_BANK_TRANSFER ? ($this->bankBic ?: null) : null,
        ];

        if ($this->editingId) {
            $method = $this->findMethod($this->editingId);
            if ($method) {
                $method->update($data);
            }
        } else {
            // If this is the first payment method, make it the default
            $isFirst = ! $company->paymentMethods()->exists();
            $data['is_default'] = $isFirst;

            PaymentMethod::create($data);
        }

        $this->resetForm();
        $this->showForm = false;
        session()->flash('payment_method_success', __('Payment method saved.'));
    }

    public function setDefault(int $id): void
    {
        $method = $this->findMethod($id);
        if (! $method) {
            return;
        }

        $company = Auth::user()->currentCompany;

        DB::transaction(function () use ($company, $method) {
            $company->paymentMethods()->update(['is_default' => false]);
            $method->update(['is_default' => true]);
        });

        session()->flash('payment_method_success', __('Payment method set as default.'));
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = true;
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->deletingId = null;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $method = $this->findMethod($this->deletingId);
        if (! $method) {
            return;
        }

        $wasDefault = $method->is_default;
        $company = Auth::user()->currentCompany;

        $method->delete();

        // Promote next available method to default if the deleted one was default
        if ($wasDefault) {
            $next = $company->paymentMethods()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        $this->confirmingDelete = false;
        $this->deletingId = null;
        session()->flash('payment_method_success', __('Payment method deleted.'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function render()
    {
        $company = Auth::user()->currentCompany;
        $paymentMethods = $company ? $company->paymentMethods()->orderByDesc('is_default')->orderBy('created_at')->get() : collect();
        $planService = app(PlanService::class);

        return view('livewire.settings.payment-methods', [
            'paymentMethods' => $paymentMethods,
            'canAdd' => $planService->canAddPaymentMethod(Auth::user()),
            'remaining' => $planService->paymentMethodsRemaining(Auth::user()),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->type = PaymentMethod::TYPE_BANK_TRANSFER;
        $this->label = '';
        $this->bankName = '';
        $this->bankIban = '';
        $this->bankBic = '';
        $this->notes = '';
    }

    private function findMethod(int $id): ?PaymentMethod
    {
        $company = Auth::user()->currentCompany;
        if (! $company) {
            return null;
        }

        return $company->paymentMethods()->find($id);
    }
}
