<?php

namespace App\Livewire\Timer;

use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActiveTimer extends Component
{
    public ?int $projectId = null;
    public string $description = '';
    public ?int $activeTimerId = null;
    public bool $isRunning = false;
    public string $elapsedTime = '00:00:00';

    public function mount(): void
    {
        $active = TimeEntry::where('user_id', Auth::id())
            ->whereNull('stopped_at')
            ->latest()
            ->first();

        if ($active) {
            $this->activeTimerId = $active->id;
            $this->projectId = $active->project_id;
            $this->description = $active->description ?? '';
            $this->isRunning = true;
        }
    }

    public function startTimer(): void
    {
        if (!$this->projectId) {
            $this->addError('projectId', 'Please select a project.');
            return;
        }

        $entry = TimeEntry::create([
            'user_id' => Auth::id(),
            'project_id' => $this->projectId,
            'description' => $this->description,
            'started_at' => now(),
        ]);

        $this->activeTimerId = $entry->id;
        $this->isRunning = true;
    }

    public function stopTimer(): void
    {
        if (!$this->activeTimerId) {
            return;
        }

        $entry = TimeEntry::where('user_id', Auth::id())->find($this->activeTimerId);
        if ($entry) {
            $entry->stop();
        }

        $this->activeTimerId = null;
        $this->isRunning = false;
        $this->description = '';
        $this->dispatch('timer-stopped');
    }

    public function render()
    {
        $projects = Project::where('user_id', Auth::id())->active()->with('client')->get();

        return view('livewire.timer.active-timer', compact('projects'));
    }
}
