<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_a_demo_user_with_known_credentials(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'demo@invoicekit.test')->firstOrFail();

        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_seeding_creates_a_demo_admin_that_can_authenticate_on_the_admin_guard(): void
    {
        $this->seed(DatabaseSeeder::class);

        Admin::where('email', 'demo@invoicekit.test')->firstOrFail();

        $this->assertTrue(Auth::guard('admin')->attempt([
            'email' => 'demo@invoicekit.test',
            'password' => 'password',
        ]));
    }
}
