<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_shows_tracked_hours_this_month(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        // 120 minutes = 2 hours this month
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => now()->startOfMonth()->addHours(2),
            'stopped_at' => now()->startOfMonth()->addHours(4),
            'duration_minutes' => 120,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->assertSet('trackedHoursThisMonth', 2);
    }

    public function test_dashboard_shows_unpaid_invoices_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->sent()->count(2)->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);
        Invoice::factory()->paid()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->assertSet('unpaidInvoicesCount', 2);
    }

    public function test_dashboard_shows_overdue_invoices_count(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->overdue()->count(3)->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->assertSet('overdueInvoicesCount', 3);
    }

    public function test_dashboard_shows_unpaid_invoices_total(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->sent()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total' => 1000.00,
        ]);
        Invoice::factory()->overdue()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total' => 500.00,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->assertSet('unpaidInvoicesTotal', 1500.0);
    }

    public function test_dashboard_shows_only_own_data(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client2 = Client::factory()->create(['user_id' => $user2->id]);

        // User 2 has overdue invoices
        Invoice::factory()->overdue()->count(5)->create([
            'user_id' => $user2->id,
            'client_id' => $client2->id,
        ]);

        // User 1 sees none
        Livewire::actingAs($user1)
            ->test(\App\Livewire\Dashboard::class)
            ->assertSet('overdueInvoicesCount', 0)
            ->assertSet('unpaidInvoicesCount', 0);
    }
}
