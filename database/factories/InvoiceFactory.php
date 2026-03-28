<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $vatRate = 19.0;
        $vatAmount = round($subtotal * $vatRate / 100, 2);

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => 'INV-'.now()->year.'-'.fake()->unique()->numerify('####'),
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency' => 'EUR',
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'vat_type' => 'standard',
            'total' => round($subtotal + $vatAmount, 2),
            'notes' => null,
            'paid_at' => null,
            'language' => 'en',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'draft']);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'sent']);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => now()->subDays(7)->toDateString(),
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);
    }
}
