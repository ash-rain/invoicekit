<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryModelTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_time_entry_is_not_running_when_stopped_at_is_set(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertFalse($entry->isRunning());
    }

    public function test_stop_method_sets_stopped_at(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => now()->subMinutes(90),
            'stopped_at' => null,
            'duration_minutes' => null,
        ]);

        $entry->stop();

        $this->assertNotNull($entry->stopped_at);
        $this->assertGreaterThanOrEqual(89, $entry->duration_minutes);
        $this->assertLessThanOrEqual(91, $entry->duration_minutes);
    }

    public function test_stop_method_persists_to_database(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => now()->subHour(),
            'stopped_at' => null,
            'duration_minutes' => null,
        ]);

        $entry->stop();

        $this->assertNotNull($entry->fresh()->stopped_at);
        $this->assertNotNull($entry->fresh()->duration_minutes);
    }

    public function test_time_entry_belongs_to_project(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertEquals($project->id, $entry->project->id);
    }
}
