<?php

namespace Database\Factories;

use App\Models\AiApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiApiKey>
 */
class AiApiKeyFactory extends Factory
{
    protected $model = AiApiKey::class;

    public function definition(): array
    {
        return [
            'provider' => 'gemini',
            'api_key' => 'AIza'.fake()->regexify('[A-Za-z0-9_\-]{35}'),
            'label' => fake()->words(2, true).' key',
            'is_active' => true,
            'request_count' => fake()->numberBetween(0, 500),
            'last_used_at' => fake()->optional()->dateTimeThisMonth(),
            'last_error_at' => null,
            'last_error_message' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withError(string $message = 'Rate limit exceeded'): static
    {
        return $this->state(fn () => [
            'last_error_at' => now(),
            'last_error_message' => $message,
        ]);
    }

    public function available(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
            'last_error_at' => null,
        ]);
    }
}
