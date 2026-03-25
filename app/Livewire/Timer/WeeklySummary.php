<?php

namespace App\Livewire\Timer;

use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WeeklySummary extends Component
{
    public Carbon $weekStart;

    public Carbon $weekEnd;

    public function mount(): void
    {
        $this->weekStart = now()->startOfWeek();
        $this->weekEnd = now()->endOfWeek();
    }

    public function previousWeek(): void
    {
        $this->weekStart->subWeek();
        $this->weekEnd->subWeek();
    }

    public function nextWeek(): void
    {
        $this->weekStart->addWeek();
        $this->weekEnd->addWeek();
    }

    public function render()
    {
        $entries = TimeEntry::where('user_id', Auth::id())
            ->whereBetween('started_at', [$this->weekStart, $this->weekEnd])
            ->whereNotNull('duration_minutes')
            ->with('project.client')
            ->get();

        $byProject = $entries
            ->groupBy('project_id')
            ->map(fn ($group) => [
                'project' => $group->first()->project,
                'minutes' => $group->sum('duration_minutes'),
                'entries' => $group->count(),
            ])
            ->sortByDesc('minutes')
            ->values();

        $totalMinutes = $entries->sum('duration_minutes');

        return view('livewire.timer.weekly-summary', compact('byProject', 'totalMinutes'));
    }
}
