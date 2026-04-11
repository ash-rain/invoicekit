<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use App\Services\InvoiceValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCompletenessTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
        ]);
        $this->user->update(['current_company_id' => $this->company->id]);
    }

    public function test_bg_client_without_eik_is_incomplete(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'name' => 'Test BG Client',
            'address' => 'ул. Тестова 1',
            'vat_number' => null,
            'registration_number' => null,
        ]);

        $service = new InvoiceValidationService;
        $result = $service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('buyer_eik_or_vat'));
    }

    public function test_bg_client_with_eik_is_complete(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'name' => 'Test BG Client',
            'address' => 'ул. Тестова 1',
            'registration_number' => '123456789',
        ]);

        $service = new InvoiceValidationService;
        $result = $service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->passes());
    }

    public function test_eu_client_without_vat_has_warning(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'DE',
            'name' => 'German Company',
            'address' => 'Berliner Str. 1',
            'vat_number' => null,
        ]);

        $service = new InvoiceValidationService;
        $result = $service->clientCompleteness($client, 'BG');

        // EU client without VAT → warning, not error
        $this->assertTrue($result->passes()); // passes (no errors)
        $this->assertNotEmpty($result->warnings());
    }

    public function test_non_eu_client_with_name_and_address_is_complete(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'US',
            'name' => 'US Corp',
            'address' => '123 Main St',
        ]);

        $service = new InvoiceValidationService;
        $result = $service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->passes());
    }

    public function test_client_missing_address_is_incomplete(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'name' => 'Test BG Client',
            'address' => null,
            'registration_number' => '123456789',
        ]);

        $service = new InvoiceValidationService;
        $result = $service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('buyer_address'));
    }
}
