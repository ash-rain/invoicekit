<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\RecurringInvoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateRecurringInvoice extends Component
{
    public ?RecurringInvoice $recurringInvoice = null;

    public ?int $clientId = null;

    public string $frequency = 'monthly';

    public string $nextSendDate = '';

    public string $currency = 'EUR';

    public string $language = 'en';

    public string $notes = '';

    public array $items = [];

    public float $subtotal = 0.0;

    public float $vatRate = 0.0;

    public float $vatAmount = 0.0;

    public float $total = 0.0;

    public function mount(?RecurringInvoice $recurringInvoice = null): void
    {
        if ($recurringInvoice && $recurringInvoice->exists) {
            if ($recurringInvoice->user_id !== Auth::id()) {
                abort(403);
            }

            $this->recurringInvoice = $recurringInvoice;
            $this->clientId = $recurringInvoice->client_id;
            $this->frequency = $recurringInvoice->frequency;
            $this->nextSendDate = $recurringInvoice->next_send_date->format('Y-m-d');
            $this->currency = $recurringInvoice->currency;
            $this->language = $recurringInvoice->language ?? 'en';
            $this->notes = $recurringInvoice->notes ?? '';
            $this->vatRate = (float) $recurringInvoice->vat_rate;
            $this->items = $recurringInvoice->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
            ])->toArray();
        } else {
            $this->nextSendDate = now()->addMonth()->format('Y-m-d');
            $this->language = Auth::user()->locale ?: 'en';
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
        array_splice($this->items, $index, 1);
        $this->recalculate();
    }

    public function updatedItems(): void
    {
        $this->recalculate();
    }

    public function updatedVatRate(): void
    {
        $this->recalculate();
    }

    public function recalculate(): void
    {
        $subtotal = 0.0;
        foreach ($this->items as $item) {
            $subtotal += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
        }

        $this->subtotal = round($subtotal, 2);
        $this->vatAmount = round($subtotal * ($this->vatRate / 100), 2);
        $this->total = round($this->subtotal + $this->vatAmount, 2);
    }

    public function save(): void
    {
        $this->validate([
            'clientId' => ['required', 'integer', 'exists:clients,id'],
            'frequency' => ['required', 'in:monthly,quarterly,annually'],
            'nextSendDate' => ['required', 'date', 'after_or_equal:today'],
            'currency' => ['required', 'string', 'max:10'],
            'vatRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->recalculate();

        DB::transaction(function () {
            $data = [
                'user_id' => Auth::id(),
                'client_id' => $this->clientId,
                'frequency' => $this->frequency,
                'next_send_date' => $this->nextSendDate,
                'currency' => $this->currency,
                'language' => $this->language,
                'notes' => $this->notes ?: null,
                'subtotal' => $this->subtotal,
                'vat_rate' => $this->vatRate,
                'vat_amount' => $this->vatAmount,
                'total' => $this->total,
                'vat_type' => 'standard',
                'active' => true,
            ];

            if ($this->recurringInvoice) {
                $this->recurringInvoice->update($data);
                $this->recurringInvoice->items()->delete();
                $recurring = $this->recurringInvoice;
            } else {
                $recurring = RecurringInvoice::create($data);
            }

            foreach ($this->items as $item) {
                $recurring->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }
        });

        $this->redirect(route('recurring-invoices.index'), navigate: true);
    }

    #[Computed]
    public function clients()
    {
        return Client::where('user_id', Auth::id())->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.invoices.create-recurring-invoice');
    }
}
