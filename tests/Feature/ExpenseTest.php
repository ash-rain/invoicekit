<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_list_requires_auth(): void
    {
        $this->get(route('expenses.index'))
            ->assertRedirect(route('login'));
    }

    public function test_expense_list_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSeeLivewire('expenses.expense-list');
    }

    public function test_expense_list_shows_only_own_expenses(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = Expense::factory()->create(['user_id' => $user->id, 'description' => 'My Office Rent']);
        $foreign = Expense::factory()->create(['user_id' => $other->id, 'description' => 'Other User Expense']);

        $this->actingAs($user)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('My Office Rent')
            ->assertDontSee('Other User Expense');
    }

    public function test_create_expense_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('expenses.create'))
            ->assertOk()
            ->assertSeeLivewire('expenses.create-expense');
    }

    public function test_edit_expense_page_renders(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('expenses.edit', $expense))
            ->assertOk()
            ->assertSeeLivewire('expenses.create-expense');
    }

    public function test_edit_expense_page_forbidden_for_other_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('expenses.edit', $expense))
            ->assertForbidden();
    }

    public function test_expense_model_has_correct_fillable_and_casts(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'description' => 'Hosting fee',
            'amount' => 29.99,
            'currency' => 'EUR',
            'category' => 'hosting',
            'date' => '2025-01-15',
            'billable' => true,
        ]);

        $this->assertEquals('Hosting fee', $expense->description);
        $this->assertEquals('29.99', $expense->amount);
        $this->assertEquals('hosting', $expense->category);
        $this->assertTrue($expense->billable);
        $this->assertEquals('2025-01-15', $expense->date->format('Y-m-d'));
    }

    public function test_expense_belongs_to_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $this->assertEquals($client->id, $expense->fresh()->client->id);
    }

    public function test_csv_export_requires_auth(): void
    {
        $this->get(route('expenses.export'))
            ->assertRedirect(route('login'));
    }

    public function test_csv_export_returns_csv_file(): void
    {
        $user = User::factory()->create();
        Expense::factory()->create(['user_id' => $user->id, 'description' => 'Server costs']);

        $response = $this->actingAs($user)
            ->get(route('expenses.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Server costs', (string) $response->streamedContent());
    }

    public function test_csv_export_excludes_other_user_expenses(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Expense::factory()->create(['user_id' => $other->id, 'description' => 'Secret expense']);

        $response = $this->actingAs($user)
            ->get(route('expenses.export'));

        $this->assertStringNotContainsString('Secret expense', (string) $response->streamedContent());
    }
}
