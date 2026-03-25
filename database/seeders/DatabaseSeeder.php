<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@invoicekit.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $clients = [
            [
                'name' => 'Acme Corp',
                'email' => 'billing@acme.example',
                'address' => "123 Main Street\nNew York, NY 10001",
                'country' => 'DE',
                'vat_number' => 'DE123456789',
                'currency' => 'EUR',
            ],
            [
                'name' => 'Bright Ideas Ltd',
                'email' => 'accounts@brightideas.example',
                'address' => "45 High Street\nLondon EC1A 1BB",
                'country' => 'FR',
                'currency' => 'EUR',
            ],
            [
                'name' => 'Skyline Solutions',
                'email' => 'finance@skyline.example',
                'address' => "77 Rue de la Paix\n75002 Paris",
                'country' => 'FR',
                'currency' => 'EUR',
            ],
        ];

        foreach ($clients as $data) {
            $client = Client::create(array_merge($data, ['user_id' => $user->id]));

            $project = Project::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'name' => $client['name'].' – Website Redesign',
                'hourly_rate' => 95.00,
                'currency' => 'EUR',
                'status' => 'active',
            ]);

            // Paid invoice
            $this->createInvoice($user, $client, $project, 'paid', now()->subMonths(2));
            // Sent invoice
            $this->createInvoice($user, $client, $project, 'sent', now()->subWeeks(1));
            // Draft invoice
            $this->createInvoice($user, $client, $project, 'draft', now());
        }
    }

    private function createInvoice(User $user, Client $client, Project $project, string $status, \DateTimeInterface $date): void
    {
        static $counter = 1;

        $items = [
            ['description' => 'Frontend development', 'quantity' => 8,  'unit_price' => 95.00],
            ['description' => 'Backend API integration', 'quantity' => 4, 'unit_price' => 95.00],
            ['description' => 'Project management',    'quantity' => 2,  'unit_price' => 75.00],
        ];

        $vatRate = 21.00;
        $subtotal = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
        $vatAmount = round($subtotal * $vatRate / 100, 2);
        $total = $subtotal + $vatAmount;

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-'.str_pad($counter++, 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'issue_date' => $date,
            'due_date' => (clone $date)->modify('+30 days'),
            'currency' => 'EUR',
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'vat_type' => 'standard',
            'total' => $total,
            'language' => 'en',
            'paid_at' => $status === 'paid' ? $date : null,
        ]);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $vatRate,
                'total' => round($item['quantity'] * $item['unit_price'] * (1 + $vatRate / 100), 2),
            ]);
        }
    }
}
