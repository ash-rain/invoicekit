<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // Access control
    // ──────────────────────────────────────────────────────────────────

    public function test_guests_cannot_access_timer(): void
    {
        $this->get(route('timer'))->assertRedirect(route('login'));
    }

    // ──────────────────────────────────────────────────────────────────
    // Start timer
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_start_timer_with_a_project(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ActiveTimer::class)
            ->set('projectId', $project->id)
            ->set('description', 'Working on feature X')
            ->call('startTimer');

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Working on feature X',
            'stopped_at' => null,
        ]);
    }

    public function test_user_cannot_start_timer_without_selecting_a_project(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ActiveTimer::class)
            ->set('projectId', null)
            ->call('startTimer')
            ->assertHasErrors(['projectId']);

        $this->assertDatabaseEmpty('time_entries');
    }

    public function test_starting_timer_sets_is_running_to_true(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ActiveTimer::class)
            ->set('projectId', $project->id)
            ->call('startTimer')
            ->assertSet('isRunning', true);
    }

    // ──────────────────────────────────────────────────────────────────
    // Stop timer
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_stop_a_running_timer(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->running()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ActiveTimer::class)
            ->call('stopTimer');

        $this->assertNotNull($entry->fresh()->stopped_at);
        $this->assertNotNull($entry->fresh()->duration_minutes);
    }

    public function test_stopping_timer_sets_is_running_to_false(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        TimeEntry::factory()->running()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ActiveTimer::class)
            ->call('stopTimer')
            ->assertSet('isRunning', false);
    }

    // ──────────────────────────────────────────────────────────────────
    // Active timer restores on mount
    // ──────────────────────────────────────────────────────────────────

    public function test_active_timer_restores_on_mount(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->running()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Ongoing task',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ActiveTimer::class)
            ->assertSet('isRunning', true)
            ->assertSet('activeTimerId', $entry->id)
            ->assertSet('description', 'Ongoing task');
    }

    // ──────────────────────────────────────────────────────────────────
    // Manual time entry
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_create_manual_time_entry(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ManualTimeEntry::class)
            ->set('projectId', $project->id)
            ->set('description', 'Past work')
            ->set('date', now()->format('Y-m-d'))
            ->set('startTime', '09:00')
            ->set('endTime', '11:00')
            ->call('save');

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Past work',
        ]);
    }

    public function test_manual_entry_requires_a_project(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Timer\ManualTimeEntry::class)
            ->set('projectId', null)
            ->call('save')
            ->assertHasErrors(['projectId']);
    }

    // ──────────────────────────────────────────────────────────────────
    // TimeEntry model
    // ──────────────────────────────────────────────────────────────────

    public function test_time_entry_is_running_when_stopped_at_is_null(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->running()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertTrue($entry->isRunning());
    }

    public function test_time_entry_stop_method_sets_stopped_at_and_duration(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => now()->subMinutes(60),
            'stopped_at' => null,
            'duration_minutes' => null,
        ]);

        $entry->stop();

        $this->assertNotNull($entry->stopped_at);
        $this->assertGreaterThanOrEqual(59, $entry->duration_minutes);
        $this->assertLessThanOrEqual(61, $entry->duration_minutes);
    }

    // ──────────────────────────────────────────────────────────────────
    // Time entry list shows only own entries
    // ──────────────────────────────────────────────────────────────────

    public function test_time_entry_list_shows_only_own_entries(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $client2 = Client::factory()->create(['user_id' => $user2->id]);
        $project1 = Project::factory()->create(['user_id' => $user1->id, 'client_id' => $client1->id]);
        $project2 = Project::factory()->create(['user_id' => $user2->id, 'client_id' => $client2->id]);

        TimeEntry::factory()->create([
            'user_id' => $user1->id,
            'project_id' => $project1->id,
            'description' => 'User1 work',
            'started_at' => now(),
            'stopped_at' => now()->addHour(),
            'duration_minutes' => 60,
        ]);
        TimeEntry::factory()->create([
            'user_id' => $user2->id,
            'project_id' => $project2->id,
            'description' => 'User2 work',
            'started_at' => now(),
            'stopped_at' => now()->addHour(),
            'duration_minutes' => 60,
        ]);

        Livewire::actingAs($user1)
            ->test(\App\Livewire\Timer\TimeEntryList::class)
            ->assertSee('User1 work')
            ->assertDontSee('User2 work');
    }
}
