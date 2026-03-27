<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getNotificationsProperty(): Collection
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->limit(20)
            ->get();
    }

    public function getUnreadCountProperty(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead(string $id): void
    {
        $url = null;

        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $url = $notification->data['url'] ?? null;
            $notification->markAsRead();
        } catch (ModelNotFoundException) {
            // Notification not found in this user's scope — silently ignore.
        }

        if ($url) {
            $this->redirect($url);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.notification-bell');
    }
}
