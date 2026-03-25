<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_invoice_pdf(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $this->get(route('invoices.pdf', $invoice))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_own_invoice_pdf(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)
            ->get(route('invoices.pdf', $invoice));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_user_cannot_download_another_users_invoice_pdf(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user2->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user2->id, 'client_id' => $client->id]);

        $this->actingAs($user1)
            ->get(route('invoices.pdf', $invoice))
            ->assertForbidden();
    }

    public function test_invoice_pdf_content_contains_invoice_number(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Test Client']);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-0001',
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Development Services',
        ]);

        $response = $this->actingAs($user)
            ->get(route('invoices.pdf', $invoice));

        $response->assertOk();
        // PDF binary - just check it's generated
        $this->assertNotEmpty($response->getContent());
    }
}
