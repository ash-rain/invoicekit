<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringInvoice>
 */
class RecurringInvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $vatRate = 19.0;
        $vatAmount = round($subtotal * $vatRate / 100, 2);

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'frequency' => fake()->randomElement(['monthly', 'quarterly', 'annually']),
            'next_send_date' => now()->addMonth()->toDateString(),
            'last_sent_date' => null,
            'currency' => 'EUR',
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => round($subtotal + $vatAmount, 2),
            'vat_type' => 'standard',
            'language' => 'en',
            'notes' => null,
            'active' => true,
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => ['frequency' => 'monthly']);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }

    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => ['next_send_date' => now()->toDateString()]);
    }
}
