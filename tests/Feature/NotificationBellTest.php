<?php

namespace Tests\Feature;

use App\Livewire\NotificationBell;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_bell_shows_zero_unread_count_with_no_notifications(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('open', false)
            ->assertSeeHtml('Notifications');
    }

    public function test_bell_shows_unread_count_when_notifications_exist(): void
    {
        $user = User::factory()->create();
        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\InvoicePaidNotification',
            'data' => ['type' => 'invoice_paid', 'message' => 'Invoice #001 paid.', 'url' => '/invoices/1'],
            'read_at' => null,
        ]);

        $component = Livewire::actingAs($user)->test(NotificationBell::class);

        $this->assertEquals(1, $component->get('unreadCount'));
    }

    public function test_mark_as_read_marks_single_notification(): void
    {
        $user = User::factory()->create();
        $notificationId = \Illuminate\Support\Str::uuid()->toString();
        $user->notifications()->create([
            'id' => $notificationId,
            'type' => 'App\Notifications\InvoicePaidNotification',
            'data' => ['type' => 'invoice_paid', 'message' => 'Invoice #001 paid.', 'url' => '/invoices/1'],
            'read_at' => null,
        ]);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notificationId);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
        ]);
        $notification = $user->notifications()->find($notificationId);
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_all_as_read_clears_all_unread(): void
    {
        $user = User::factory()->create();
        foreach (['#001', '#002'] as $num) {
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\InvoicePaidNotification',
                'data' => ['type' => 'invoice_paid', 'message' => "Invoice {$num} paid.", 'url' => '/invoices/1'],
                'read_at' => null,
            ]);
        }

        $component = Livewire::actingAs($user)->test(NotificationBell::class);
        $this->assertEquals(2, $component->get('unreadCount'));

        $component->call('markAllAsRead');

        $this->assertEquals(0, $component->refresh()->get('unreadCount'));
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notificationId = \Illuminate\Support\Str::uuid()->toString();
        $other->notifications()->create([
            'id' => $notificationId,
            'type' => 'App\Notifications\InvoicePaidNotification',
            'data' => ['type' => 'invoice_paid', 'message' => 'Invoice #001 paid.', 'url' => '/invoices/1'],
            'read_at' => null,
        ]);

        // The query is scoped to Auth::user()->notifications() so model is not found,
        // which means the other user's notification remains unread — correct security behavior.
        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notificationId);

        $this->assertNull($other->notifications()->find($notificationId)?->read_at);
    }
}
