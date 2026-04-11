<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 40);
        $unitPrice = fake()->randomFloat(2, 10, 300);
        $total = round($quantity * $unitPrice, 2);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->sentence(4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'vat_rate' => 19.0,
            'total' => $total,
            'vat_rate_key' => 'standard',
        ];
    }
}
