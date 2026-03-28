<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'address_line1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->randomElement(['BG', 'DE', 'FR', 'RO', 'PL', 'IT', 'NL']),
            'default_currency' => 'EUR',
            'default_payment_terms' => 30,
            'vat_exempt' => false,
            'vat_exempt_notice_language' => 'local',
        ];
    }
}
