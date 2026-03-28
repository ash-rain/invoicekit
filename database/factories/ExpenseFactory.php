<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => null,
            'project_id' => null,
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 10, 2000),
            'currency' => 'EUR',
            'category' => fake()->randomElement(['software', 'hardware', 'travel', 'hosting', 'marketing', 'other']),
            'receipt_file' => null,
            'date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'billable' => false,
            'invoice_id' => null,
        ];
    }

    public function billable(): static
    {
        return $this->state(fn (array $attributes) => ['billable' => true]);
    }

    public function software(): static
    {
        return $this->state(fn (array $attributes) => ['category' => 'software']);
    }

    public function travel(): static
    {
        return $this->state(fn (array $attributes) => ['category' => 'travel']);
    }
}
