<?php

namespace App\Livewire\Timer;

use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManualTimeEntry extends Component
{
    public ?int $projectId = null;

    public string $description = '';

    public string $date = '';

    public string $startTime = '';

    public string $endTime = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function save(): void
    {
        $this->validate([
            'projectId' => 'required|integer',
            'date' => 'required|date',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'description' => 'nullable|string|max:255',
        ]);

        $startedAt = \Carbon\Carbon::parse("{$this->date} {$this->startTime}");
        $stoppedAt = \Carbon\Carbon::parse("{$this->date} {$this->endTime}");

        TimeEntry::create([
            'user_id' => Auth::id(),
            'project_id' => $this->projectId,
            'description' => $this->description,
            'started_at' => $startedAt,
            'stopped_at' => $stoppedAt,
            'duration_minutes' => (int) $startedAt->diffInMinutes($stoppedAt),
        ]);

        $this->reset(['projectId', 'description', 'startTime', 'endTime']);
        $this->date = now()->toDateString();
        $this->dispatch('entry-saved');
    }

    public function render()
    {
        $projects = Project::where('user_id', Auth::id())->active()->with('client')->get();

        return view('livewire.timer.manual-time-entry', compact('projects'));
    }
}
