<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProjectDetail extends Component
{
    use WithPagination;

    public Project $project;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);
        $this->project = $project;
    }

    public function deleteEntry(int $entryId): void
    {
        $entry = TimeEntry::where('user_id', Auth::id())
            ->where('project_id', $this->project->id)
            ->findOrFail($entryId);
        $entry->delete();
    }

    public function render()
    {
        $this->project->loadMissing('client');

        $totalMinutes = TimeEntry::where('project_id', $this->project->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('duration_minutes')
            ->sum('duration_minutes');

        $totalHours = round($totalMinutes / 60, 2);

        $totalEarnings = $this->project->hourly_rate
            ? round($totalHours * (float) $this->project->hourly_rate, 2)
            : null;

        $entries = TimeEntry::where('project_id', $this->project->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('stopped_at')
            ->orderByDesc('started_at')
            ->paginate(20);

        return view('livewire.projects.project-detail', compact(
            'totalHours',
            'totalEarnings',
            'entries',
        ));
    }
}
