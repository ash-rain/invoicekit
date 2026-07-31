<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsersTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_shows_invoices_count(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        Invoice::factory()->count(3)->for($user)->for($client)->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(ListUsers::class)
            ->assertTableColumnExists('invoices_count')
            ->assertTableColumnStateSet('invoices_count', 3, record: $user);
    }
}
