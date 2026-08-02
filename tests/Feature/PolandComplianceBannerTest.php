<?php

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceList;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PolandComplianceBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_polish_company_sees_ksef_grace_period_banner(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'country' => 'PL',
        ]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(InvoiceList::class)
            ->assertSee('KSeF');
    }

    public function test_non_polish_company_does_not_see_ksef_grace_period_banner(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'country' => 'BG',
        ]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(InvoiceList::class)
            ->assertDontSee('KSeF');
    }
}
