<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

    // Line items: array of ['description', 'quantity', 'unit_price']
    public array $items = [];

    public bool $vatExemptActive = false;

    public bool $vatExemptOverride = false;

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
            $this->vatType = $invoice->vat_type ?? 'standard';
            $this->vatExemptOverride = $this->vatExemptActive && ! $invoice->vat_exempt_applied;
            $this->items = $invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
            ])->toArray();
        } else {
            $this->issueDate = now()->format('Y-m-d');
            $this->dueDate = now()->addDays(30)->format('Y-m-d');
            $this->invoiceNumber = Invoice::generateNumber($userId);
            $this->addItem();
        }

        $this->recalculate();
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => '1', 'unit_price' => '0.00'];
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
            'language' => ['required', 'string', 'in:en,bg'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
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
                session()->flash('error', 'You have reached the invoice limit for your plan this month. Please upgrade to create more invoices.');
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

            $data = [
                'user_id' => Auth::id(),
                'client_id' => $this->clientId,
                'invoice_number' => $this->invoiceNumber,
                'status' => $this->invoice?->status ?? 'draft',
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate,
                'currency' => $this->currency,
                'language' => $this->language,
                'subtotal' => $this->subtotal,
                'vat_rate' => $this->vatRate,
                'vat_amount' => $this->vatAmount,
                'vat_type' => $this->vatType,
                'total' => $this->total,
                'notes' => $this->notes ?: null,
                'vat_exempt_applied' => $isExempt,
                'vat_exempt_notice' => $exemptNotice,
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
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'vat_rate' => $this->vatRate,
                    'total' => $lineTotal,
                ]);
            }
        });

        session()->flash('success', 'Invoice saved successfully.');
        $this->redirect(route('invoices.index'), navigate: true);
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();

        return view('livewire.invoices.create-invoice', [
            'clients' => $clients,
            'selected' => $this->selectedClient(),
        ]);
    }
}
