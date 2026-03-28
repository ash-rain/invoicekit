<?php

namespace App\Livewire\Timer;

use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ActiveTimer extends Component
{
    public ?int $projectId = null;

    public string $description = '';

    public ?int $activeTimerId = null;

    public bool $isRunning = false;

    public string $elapsedTime = '00:00:00';

    public ?string $startedAt = null;

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
            $this->startedAt = $active->started_at->toIso8601String();
            $this->elapsedTime = $this->computeElapsed($active->started_at);
        }
    }

    public function startTimer(): void
    {
        if (! $this->projectId) {
            $this->addError('projectId', __('Please select a project.'));

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
        $this->startedAt = $entry->started_at->toIso8601String();
        $this->elapsedTime = '00:00:00';
    }

    public function stopTimer(): void
    {
        if (! $this->activeTimerId) {
            return;
        }

        $entry = TimeEntry::where('user_id', Auth::id())->find($this->activeTimerId);
        if ($entry) {
            $entry->stop();
        }

        $this->activeTimerId = null;
        $this->isRunning = false;
        $this->startedAt = null;
        $this->description = '';
        $this->elapsedTime = '00:00:00';
        $this->dispatch('timer-stopped');
    }

    public function tick(): void
    {
        if ($this->isRunning && $this->activeTimerId) {
            $entry = TimeEntry::where('user_id', Auth::id())->find($this->activeTimerId);
            if ($entry) {
                $this->elapsedTime = $this->computeElapsed($entry->started_at);
            }
        }
    }

    private function computeElapsed(\Carbon\Carbon $startedAt): string
    {
        $seconds = (int) now()->diffInSeconds($startedAt);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    public function render()
    {
        $projects = Project::where('user_id', Auth::id())->active()->with('client')->get();

        return view('livewire.timer.active-timer', compact('projects'));
    }
}
