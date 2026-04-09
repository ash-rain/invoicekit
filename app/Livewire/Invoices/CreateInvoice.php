<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Services\EuVatService;
use App\Services\PlanService;
use App\Services\VatExemptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateInvoice extends Component
{
    public ?Invoice $invoice = null;

    // Invoice header
    public ?int $clientId = null;

    public string $issueDate = '';

    public string $dueDate = '';

    public string $notes = '';

    public string $currency = 'EUR';

    public string $invoiceNumber = '';

    public string $language = 'en';

    public string $invoiceTemplate = 'classic';

    // Document type & BG compliance fields
    public string $documentType = 'invoice';

    public ?int $originalInvoiceId = null;

    public string $taxEventDate = '';

    public string $issuedByName = '';

    public string $receivedByName = '';

    // Line items: array of ['description', 'unit', 'quantity', 'unit_price']
    public array $items = [];

    public bool $vatExemptActive = false;

    public bool $vatExemptOverride = false;

    public ?int $paymentMethodId = null;

    // Seller VAT country (from user's current company or fallback)
    public string $sellerCountry = 'BG';

    // Computed totals (updated reactively)
    public float $subtotal = 0.0;

    public float $vatRate = 0.0;

    public float $vatAmount = 0.0;

    public float $total = 0.0;

    public string $vatType = 'standard';

    public function mount(?Invoice $invoice = null): void
    {
        $userId = Auth::id();
        $user = Auth::user();
        $company = $user->currentCompany;

        $this->sellerCountry = $company?->country ?? 'BG';
        $this->vatExemptActive = (bool) ($company?->vat_exempt ?? false);
        $this->paymentMethodId = $company?->defaultPaymentMethod?->id;

        if ($invoice && $invoice->exists) {
            $this->authorize('update', $invoice);
            $this->invoice = $invoice;
            $this->clientId = $invoice->client_id;
            $this->issueDate = $invoice->issue_date->format('Y-m-d');
            $this->dueDate = $invoice->due_date->format('Y-m-d');
            $this->notes = $invoice->notes ?? '';
            $this->currency = $invoice->currency;
            $this->invoiceNumber = $invoice->invoice_number;
            $this->language = $invoice->language ?? 'en';
            $this->invoiceTemplate = $invoice->template ?? $company?->invoice_template ?? 'classic';
            $this->vatType = $invoice->vat_type ?? 'standard';
            $this->vatExemptOverride = $this->vatExemptActive && ! $invoice->vat_exempt_applied;
            $this->documentType = $invoice->document_type ?? 'invoice';
            $this->originalInvoiceId = $invoice->original_invoice_id;
            $this->taxEventDate = $invoice->tax_event_date?->format('Y-m-d') ?? '';
            $this->issuedByName = $invoice->issued_by_name ?? '';
            $this->receivedByName = $invoice->received_by_name ?? '';
            $this->paymentMethodId = $invoice->payment_method_id ?? $this->paymentMethodId;
            $this->items = $invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'unit' => $item->unit ?? '',
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
            ])->toArray();
        } else {
            $this->issueDate = now()->format('Y-m-d');
            $this->dueDate = now()->addDays(30)->format('Y-m-d');
            $this->invoiceNumber = Invoice::generateNumber($userId, $company);
            // Pre-select language from user locale preference
            $this->language = Auth::user()->locale ?: 'en';
            $this->invoiceTemplate = $company?->invoice_template ?? 'classic';

            // Auto-apply BG compliance defaults
            if ($this->sellerCountry === 'BG') {
                $this->taxEventDate = now()->format('Y-m-d');
                $this->issuedByName = $company?->issued_by_default_name ?? Auth::user()->name;
                if ($company?->default_currency) {
                    $this->currency = $company->default_currency;
                }
            }

            $this->addItem();
        }

        $this->recalculate();
    }

    public function addItem(): void
    {
        $unit = ($this->sellerCountry === 'BG') ? 'бр.' : '';
        $this->items[] = ['description' => '', 'unit' => $unit, 'quantity' => '1', 'unit_price' => '0.00'];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalculate();
    }

    public function updatedClientId(): void
    {
        $this->recalculate();
        $client = $this->selectedClient();
        if ($client) {
            $this->currency = $client->currency;
            if ($client->default_language) {
                $this->language = $client->default_language;
            } else {
                $this->language = Auth::user()->locale ?: 'en';
            }
        } else {
            $this->language = Auth::user()->locale ?: 'en';
        }
    }

    public function updatedItems(): void
    {
        $this->recalculate();
    }

    public function updatedVatExemptOverride(): void
    {
        $this->recalculate();
    }

    private function selectedClient(): ?Client
    {
        if (! $this->clientId) {
            return null;
        }

        return Client::where('user_id', Auth::id())->find($this->clientId);
    }

    private function recalculate(): void
    {
        $subtotal = 0.0;
        foreach ($this->items as $item) {
            $qty = max(0.0, (float) ($item['quantity'] ?? 0));
            $price = max(0.0, (float) ($item['unit_price'] ?? 0));
            $subtotal += $qty * $price;
        }

        $client = $this->selectedClient();
        $vatResult = ['rate' => 0.0, 'amount' => 0.0, 'type' => 'standard'];

        if ($client) {
            /** @var EuVatService $vatService */
            $vatService = app(EuVatService::class);
            $vatResult = $vatService->calculateVat(
                $this->sellerCountry,
                $client->country,
                ! empty($client->vat_number),
                $subtotal,
                $this->vatExemptActive && ! $this->vatExemptOverride
            );
        }

        $this->subtotal = round($subtotal, 2);
        $this->vatRate = $vatResult['rate'];
        $this->vatAmount = $vatResult['amount'];
        $this->vatType = $vatResult['type'];
        $this->total = round($subtotal + $vatResult['amount'], 2);
    }

    protected function rules(): array
    {
        return [
            'clientId' => ['required', 'integer'],
            'invoiceNumber' => ['required', 'string', 'max:50'],
            'issueDate' => ['required', 'date'],
            'dueDate' => ['required', 'date', 'after_or_equal:'.($this->issueDate ?: now()->format('Y-m-d'))],
            'currency' => ['required', 'string', 'max:3'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'language' => ['required', 'string', 'in:'.implode(',', config('invoicekit.supported_languages', ['en']))],
            'invoiceTemplate' => ['required', 'string', 'in:'.implode(',', array_keys(app(\App\Services\InvoiceTemplateService::class)->getAvailableTemplates()))],
            'documentType' => ['required', 'string', 'in:invoice,credit_note,debit_note,proforma'],
            'originalInvoiceId' => ['nullable', 'integer', 'exists:invoices,id'],
            'taxEventDate' => ['nullable', 'date'],
            'issuedByName' => ['nullable', 'string', 'max:200'],
            'receivedByName' => ['nullable', 'string', 'max:200'],
            'paymentMethodId' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();
        $this->recalculate();

        // Enforce plan limit when creating a new invoice
        if (! $this->invoice || ! $this->invoice->exists) {
            $planService = app(PlanService::class);
            if (! $planService->canCreateInvoice(Auth::user())) {
                session()->flash('error', __('You have reached the invoice limit for your plan this month. Please upgrade to create more invoices.'));
                $this->redirect(route('billing.index'), navigate: true);

                return;
            }
        }

        DB::transaction(function () {
            $isExempt = $this->vatExemptActive && ! $this->vatExemptOverride;
            $exemptNotice = null;
            if ($isExempt) {
                $company = Auth::user()->currentCompany;
                $exemptNotice = app(VatExemptionService::class)->getInvoiceNotice(
                    $this->sellerCountry,
                    $company?->vat_exempt_notice_language ?? 'local'
                );
            }

            // Calculate BGN VAT equivalent when seller is BG and currency is not BGN
            $vatAmountBgn = null;
            if ($this->sellerCountry === 'BG' && $this->currency !== 'BGN' && $this->vatAmount > 0) {
                $eurToBgn = 1.95583;
                $vatAmountBgn = $this->currency === 'EUR'
                    ? round($this->vatAmount * $eurToBgn, 2)
                    : null; // non-EUR foreign currency — no automatic conversion
            }

            $data = [
                'user_id' => Auth::id(),
                'client_id' => $this->clientId,
                'invoice_number' => $this->invoiceNumber,
                'status' => $this->invoice?->status ?? ($this->documentType === 'proforma' ? 'draft' : 'draft'),
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate,
                'currency' => $this->currency,
                'language' => $this->language,
                'template' => $this->invoiceTemplate,
                'subtotal' => $this->subtotal,
                'vat_rate' => $this->vatRate,
                'vat_amount' => $this->vatAmount,
                'vat_type' => $this->vatType,
                'total' => $this->total,
                'notes' => $this->notes ?: null,
                'vat_exempt_applied' => $isExempt,
                'vat_exempt_notice' => $exemptNotice,
                'document_type' => $this->documentType,
                'original_invoice_id' => $this->originalInvoiceId,
                'tax_event_date' => $this->taxEventDate ?: null,
                'issued_by_name' => $this->issuedByName ?: null,
                'received_by_name' => $this->receivedByName ?: null,
                'vat_amount_bgn' => $vatAmountBgn,
                'payment_method_id' => $this->paymentMethodId,
                'payment_method_snapshot' => $this->paymentMethodId
                    ? PaymentMethod::find($this->paymentMethodId)?->toSnapshot()
                    : null,
            ];

            if ($this->invoice && $this->invoice->exists) {
                $this->invoice->update($data);
                $this->invoice->items()->delete();
            } else {
                $this->invoice = Invoice::create($data);
            }

            foreach ($this->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $lineTotal = round($qty * $price, 2);

                InvoiceItem::create([
                    'invoice_id' => $this->invoice->id,
                    'description' => $item['description'],
                    'unit' => ($item['unit'] ?? '') ?: null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'vat_rate' => $this->vatRate,
                    'total' => $lineTotal,
                ]);
            }
        });

        session()->flash('success', __('Invoice saved successfully.'));
        $this->redirect(route('invoices.index'), navigate: true);
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();

        $company = Auth::user()->currentCompany;
        $paymentMethods = $company ? $company->paymentMethods()->orderByDesc('is_default')->get() : collect();

        return view('livewire.invoices.create-invoice', [
            'clients' => $clients,
            'selected' => $this->selectedClient(),
            'localeNames' => config('invoicekit.locale_names', []),
            'supportedLanguages' => config('invoicekit.supported_languages', ['en']),
            'templates' => app(\App\Services\InvoiceTemplateService::class)->getAvailableTemplates(),
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
