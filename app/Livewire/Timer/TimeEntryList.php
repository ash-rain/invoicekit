<?php

namespace App\Livewire\Timer;

use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeEntryList extends Component
{
    public function deleteEntry(int $entryId): void
    {
        TimeEntry::where('user_id', Auth::id())->findOrFail($entryId)->delete();
    }

    public function render()
    {
        $entries = TimeEntry::where('user_id', Auth::id())
            ->whereNotNull('stopped_at')
            ->with('project.client')
            ->orderByDesc('started_at')
            ->get()
            ->groupBy(fn ($e) => $e->started_at->toDateString());

        $totalsByProject = TimeEntry::where('user_id', Auth::id())
            ->whereNotNull('duration_minutes')
            ->with('project')
            ->get()
            ->groupBy('project_id')
            ->map(fn ($group) => [
                'project' => $group->first()->project,
                'minutes' => $group->sum('duration_minutes'),
            ])
            ->sortByDesc('minutes')
            ->values();

        return view('livewire.timer.time-entry-list', compact('entries', 'totalsByProject'));
    }
}
