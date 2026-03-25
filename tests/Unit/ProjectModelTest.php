<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_only_active_projects(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Project::factory()->create([
            'user_id' => $user->id, 'client_id' => $client->id, 'status' => 'active',
        ]);
        Project::factory()->archived()->create([
            'user_id' => $user->id, 'client_id' => $client->id,
        ]);

        $activeProjects = Project::active()->get();

        $this->assertCount(1, $activeProjects);
        $this->assertEquals('active', $activeProjects->first()->status);
    }

    public function test_project_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $this->assertEquals($user->id, $project->user->id);
    }

    public function test_project_belongs_to_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $this->assertEquals($client->id, $project->client->id);
    }

    public function test_project_has_time_entries_relationship(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $project->timeEntries());
    }
}
