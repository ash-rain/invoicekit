<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProjectList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tab = 'active';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    public function archiveProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('update', $project);
        $project->update(['status' => 'archived']);
    }

    public function restoreProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('update', $project);
        $project->update(['status' => 'active']);
    }

    public function deleteProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('delete', $project);
        $project->delete();
    }

    public function render()
    {
        $projects = Project::where('user_id', Auth::id())
            ->where('status', $this->tab)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with('client')
            ->withCount('timeEntries')
            ->withSum(['timeEntries as total_minutes' => fn($q) => $q->whereNotNull('duration_minutes')], 'duration_minutes')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.projects.project-list', compact('projects'));
    }
}
