<?php

namespace Tests\Feature;

use App\Livewire\Projects\CreateEditProject;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Projects\ProjectList;
use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // Access control
    // ──────────────────────────────────────────────────────────────────

    public function test_guests_cannot_access_projects_index(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_projects_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk();
    }

    public function test_authenticated_users_can_access_create_project_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('projects.create'))
            ->assertOk();
    }

    public function test_guests_cannot_access_project_detail(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        $this->get(route('projects.show', $project))->assertRedirect(route('login'));
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────
    // Project creation
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_create_a_project(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create(['currency' => 'EUR']);

        Livewire::actingAs($user)
            ->test(CreateEditProject::class)
            ->set('name', 'Website Redesign')
            ->set('client_id', $client->id)
            ->set('hourly_rate', '80')
            ->set('currency', 'EUR')
            ->call('save');

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Website Redesign',
            'currency' => 'EUR',
            'status' => 'active',
        ]);
    }

    public function test_project_name_is_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateEditProject::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_project_currency_must_be_supported(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateEditProject::class)
            ->set('name', 'Test')
            ->set('currency', 'GBP')
            ->call('save')
            ->assertHasErrors(['currency' => 'in']);
    }

    public function test_hourly_rate_must_be_numeric(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateEditProject::class)
            ->set('name', 'Test')
            ->set('hourly_rate', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['hourly_rate' => 'numeric']);
    }

    public function test_user_cannot_assign_another_users_client(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreignClient = Client::factory()->for($other)->create();

        Livewire::actingAs($user)
            ->test(CreateEditProject::class)
            ->set('name', 'Test')
            ->set('client_id', $foreignClient->id)
            ->call('save')
            ->assertHasErrors(['client_id']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Currency suggestion from client
    // ──────────────────────────────────────────────────────────────────

    public function test_currency_is_suggested_from_client_on_create(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create(['currency' => 'BGN']);

        Livewire::actingAs($user)
            ->test(CreateEditProject::class)
            ->set('client_id', $client->id)
            ->assertSet('currency', 'BGN');
    }

    public function test_currency_is_not_overridden_on_edit(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create(['currency' => 'BGN']);
        $project = Project::factory()->for($user)->create(['currency' => 'USD', 'client_id' => $client->id]);

        $otherClient = Client::factory()->for($user)->create(['currency' => 'PLN']);

        Livewire::actingAs($user)
            ->test(CreateEditProject::class, ['project' => $project])
            ->set('client_id', $otherClient->id)
            ->assertSet('currency', 'USD'); // not overridden in edit mode
    }

    // ──────────────────────────────────────────────────────────────────
    // Project editing
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_edit_own_project(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $project = Project::factory()->for($user)->for($client)->create(['name' => 'Old Name']);

        Livewire::actingAs($user)
            ->test(CreateEditProject::class, ['project' => $project])
            ->set('name', 'New Name')
            ->call('save');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_cannot_edit_another_users_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->for($owner)->create();
        $project = Project::factory()->for($owner)->for($client)->create();

        Livewire::actingAs($other)
            ->test(CreateEditProject::class, ['project' => $project])
            ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────
    // Archive / restore / delete
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_archive_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create(['status' => 'active']);

        Livewire::actingAs($user)
            ->test(ProjectList::class)
            ->call('archiveProject', $project->id);

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'archived']);
    }

    public function test_user_can_restore_an_archived_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->archived()->create();

        Livewire::actingAs($user)
            ->test(ProjectList::class)
            ->call('restoreProject', $project->id);

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'active']);
    }

    public function test_user_can_delete_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ProjectList::class)
            ->call('deleteProject', $project->id);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_archive_another_users_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->for($owner)->create();

        Livewire::actingAs($other)
            ->test(ProjectList::class)
            ->call('archiveProject', $project->id)
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->for($owner)->create();

        Livewire::actingAs($other)
            ->test(ProjectList::class)
            ->call('deleteProject', $project->id)
            ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────
    // Project list search + tabs
    // ──────────────────────────────────────────────────────────────────

    public function test_project_list_shows_only_active_by_default(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user)->create(['name' => 'Active One', 'status' => 'active']);
        Project::factory()->for($user)->archived()->create(['name' => 'Archived One']);

        Livewire::actingAs($user)
            ->test(ProjectList::class)
            ->assertSee('Active One')
            ->assertDontSee('Archived One');
    }

    public function test_archived_tab_shows_archived_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user)->create(['name' => 'Active One', 'status' => 'active']);
        Project::factory()->for($user)->archived()->create(['name' => 'Archived One']);

        Livewire::actingAs($user)
            ->test(ProjectList::class)
            ->set('tab', 'archived')
            ->assertSee('Archived One')
            ->assertDontSee('Active One');
    }

    public function test_project_list_search_filters_by_name(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user)->create(['name' => 'Website Redesign']);
        Project::factory()->for($user)->create(['name' => 'Mobile App']);

        Livewire::actingAs($user)
            ->test(ProjectList::class)
            ->set('search', 'Website')
            ->assertSee('Website Redesign')
            ->assertDontSee('Mobile App');
    }

    // ──────────────────────────────────────────────────────────────────
    // Project detail
    // ──────────────────────────────────────────────────────────────────

    public function test_project_detail_shows_stats(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create([
            'hourly_rate' => '50.00',
            'currency' => 'EUR',
        ]);
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'duration_minutes' => 120,
            'stopped_at' => now(),
            'started_at' => now()->subHours(2),
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDetail::class, ['project' => $project])
            ->assertSee('2.0') // 2 hours
            ->assertSee('100.00'); // 2h * €50
    }

    public function test_project_detail_shows_time_entries(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Code review',
            'duration_minutes' => 60,
            'stopped_at' => now(),
            'started_at' => now()->subHour(),
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDetail::class, ['project' => $project])
            ->assertSee('Code review');
    }

    public function test_user_can_delete_time_entry_from_project_detail(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'stopped_at' => now(),
            'started_at' => now()->subMinutes(30),
            'duration_minutes' => 30,
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDetail::class, ['project' => $project])
            ->call('deleteEntry', $entry->id);

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }
}
