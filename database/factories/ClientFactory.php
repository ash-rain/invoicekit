<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        $euCountries = ['AT', 'BE', 'BG', 'HR', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'HU', 'IE', 'IT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'];

        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'country' => fake()->randomElement($euCountries),
            'vat_number' => null,
            'currency' => fake()->randomElement(['EUR', 'USD', 'BGN', 'RON', 'PLN', 'CZK', 'HUF']),
        ];
    }

    public function withVatNumber(): static
    {
        return $this->state(fn (array $attributes) => [
            'vat_number' => 'DE'.fake()->numerify('#########'),
            'country' => 'DE',
        ]);
    }

    public function eu(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => fake()->randomElement(['AT', 'BE', 'BG', 'HR', 'CZ', 'FR', 'DE', 'HU', 'IT', 'NL', 'PL', 'PT', 'RO', 'ES', 'SE']),
        ]);
    }

    public function nonEu(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => fake()->randomElement(['US', 'GB', 'CH', 'NO', 'AU', 'CA']),
            'vat_number' => null,
        ]);
    }
}
