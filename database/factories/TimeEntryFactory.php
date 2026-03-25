<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $stoppedAt = (clone $startedAt)->modify('+' . fake()->numberBetween(30, 480) . ' minutes');

        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'description' => fake()->sentence(),
            'started_at' => $startedAt,
            'stopped_at' => $stoppedAt,
            'duration_minutes' => (int) (($stoppedAt->getTimestamp() - $startedAt->getTimestamp()) / 60),
        ];
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => now()->subMinutes(30),
            'stopped_at' => null,
            'duration_minutes' => null,
        ]);
    }

    public function thisMonth(): static
    {
        $startedAt = fake()->dateTimeBetween(now()->startOfMonth(), now());
        $stoppedAt = (clone $startedAt)->modify('+' . fake()->numberBetween(30, 480) . ' minutes');

        return $this->state(fn (array $attributes) => [
            'started_at' => $startedAt,
            'stopped_at' => $stoppedAt,
            'duration_minutes' => (int) (($stoppedAt->getTimestamp() - $startedAt->getTimestamp()) / 60),
        ]);
    }
}
