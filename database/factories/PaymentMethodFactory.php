<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => PaymentMethod::TYPE_BANK_TRANSFER,
            'label' => fake()->company().' Account',
            'is_default' => false,
            'bank_name' => fake()->company().' Bank',
            'bank_iban' => fake()->iban(),
            'bank_bic' => fake()->swiftBicNumber(),
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn () => [
            'type' => PaymentMethod::TYPE_BANK_TRANSFER,
            'bank_name' => fake()->company().' Bank',
            'bank_iban' => fake()->iban(),
            'bank_bic' => fake()->swiftBicNumber(),
        ]);
    }

    public function stripe(string $connectId = 'acct_test123'): static
    {
        return $this->state(fn () => [
            'type' => PaymentMethod::TYPE_STRIPE,
            'label' => 'Stripe',
            'stripe_connect_id' => $connectId,
            'bank_name' => null,
            'bank_iban' => null,
            'bank_bic' => null,
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn () => [
            'type' => PaymentMethod::TYPE_CASH,
            'label' => 'Cash',
            'bank_name' => null,
            'bank_iban' => null,
            'bank_bic' => null,
        ]);
    }
}
