<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MixedVatRateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_vat_summary_computed_for_single_rate_invoice(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'BG']);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'BG', 'registration_number' => '123456789']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'subtotal' => 1000.00,
            'vat_rate' => 20.00,
            'vat_amount' => 200.00,
            'total' => 1200.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 10,
            'unit_price' => 100.00,
            'vat_rate' => 20.00,
            'vat_rate_key' => 'standard',
            'total' => 1000.00,
        ]);

        // Compute summary manually
        $summary = $this->computeVatSummary($invoice);

        $this->assertCount(1, $summary);
        $this->assertSame(20.0, (float) $summary[0]['rate']);
        $this->assertSame(1000.0, (float) $summary[0]['base']);
        $this->assertSame(200.0, (float) $summary[0]['vat']);
    }

    public function test_vat_summary_computed_for_mixed_rate_invoice(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'BG']);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'BG', 'registration_number' => '987654321']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'subtotal' => 1100.00,
            'vat_rate' => null, // mixed rate
            'vat_amount' => 281.00, // 200 from 20% + 81 from 9%
            'total' => 1381.00,
        ]);

        // Item 1: 1000 at 20%
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 10,
            'unit_price' => 100.00,
            'vat_rate' => 20.00,
            'vat_rate_key' => 'standard',
            'total' => 1000.00,
        ]);

        // Item 2: 100 at 9%
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 9.00,
            'vat_rate_key' => 'reduced_tourism',
            'total' => 100.00,
        ]);

        $summary = $this->computeVatSummary($invoice);

        $this->assertCount(2, $summary);

        $rates = array_column($summary, 'rate');
        $this->assertContains(20.0, $rates);
        $this->assertContains(9.0, $rates);

        $group20 = array_values(array_filter($summary, fn ($g) => (float) $g['rate'] === 20.0))[0];
        $this->assertSame(1000.0, (float) $group20['base']);
        $this->assertSame(200.0, (float) $group20['vat']);

        $group9 = array_values(array_filter($summary, fn ($g) => (float) $g['rate'] === 9.0))[0];
        $this->assertSame(100.0, (float) $group9['base']);
        $this->assertEqualsWithDelta(9.0, (float) $group9['vat'], 0.01);
    }

    public function test_vat_summary_stored_on_invoice(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'vat_summary' => [
                ['rate' => 20.0, 'base' => 1000.0, 'vat' => 200.0, 'label' => 'Standard 20%'],
            ],
        ]);

        $invoice->refresh();
        $this->assertIsArray($invoice->vat_summary);
        $this->assertCount(1, $invoice->vat_summary);
        $this->assertSame(20.0, (float) $invoice->vat_summary[0]['rate']);
    }

    /**
     * Helper: compute VAT summary from invoice items (the same logic CreateInvoice should use)
     */
    private function computeVatSummary(Invoice $invoice): array
    {
        $groups = [];
        foreach ($invoice->items as $item) {
            $rate = (float) $item->vat_rate;
            $base = (float) $item->total;
            $key = (string) $rate;
            if (! isset($groups[$key])) {
                $groups[$key] = ['rate' => $rate, 'base' => 0.0, 'vat' => 0.0, 'label' => "VAT {$rate}%"];
            }
            $groups[$key]['base'] += $base;
            $groups[$key]['vat'] += round($base * $rate / 100, 2);
        }

        return array_values($groups);
    }
}
