<?php

namespace Tests\Feature;

use App\Models\DocumentImport;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiImportLimitTest extends TestCase
{
    use RefreshDatabase;

    private PlanService $planService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->planService = app(PlanService::class);
    }

    // ------ PlanService: aiImportDailyLimit ------

    public function test_free_user_daily_limit_matches_config(): void
    {
        $user = User::factory()->free()->create();

        $this->assertSame((int) config('ai.limits.free'), $this->planService->aiImportDailyLimit($user));
    }

    public function test_starter_user_daily_limit_matches_config(): void
    {
        $user = User::factory()->starter()->create();

        $this->assertSame((int) config('ai.limits.starter'), $this->planService->aiImportDailyLimit($user));
    }

    public function test_pro_user_has_no_daily_limit(): void
    {
        $user = User::factory()->pro()->create();

        $this->assertNull($this->planService->aiImportDailyLimit($user));
    }

    public function test_user_with_own_key_has_no_daily_limit(): void
    {
        $user = User::factory()->free()->withGeminiKey()->create();

        $this->assertNull($this->planService->aiImportDailyLimit($user));
    }

    // ------ PlanService: aiImportsTodayCount ------

    public function test_counts_only_todays_system_key_imports(): void
    {
        $user = User::factory()->free()->create();

        // Today, system key
        DocumentImport::factory()->count(2)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        // Today, user own key — should NOT count
        DocumentImport::factory()->create([
            'user_id' => $user->id,
            'used_own_key' => true,
            'created_at' => now(),
        ]);

        // Yesterday — should NOT count
        DocumentImport::factory()->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now()->subDay(),
        ]);

        $this->assertSame(2, $this->planService->aiImportsTodayCount($user));
    }

    // ------ PlanService: canImportDocument ------

    public function test_free_user_can_import_below_limit(): void
    {
        $user = User::factory()->free()->create();

        DocumentImport::factory()->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertTrue($this->planService->canImportDocument($user));
    }

    public function test_free_user_blocked_when_at_limit(): void
    {
        $user = User::factory()->free()->create();
        $limit = (int) config('ai.limits.free');

        DocumentImport::factory()->count($limit)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertFalse($this->planService->canImportDocument($user));
    }

    public function test_starter_user_blocked_when_at_limit(): void
    {
        $user = User::factory()->starter()->create();
        $limit = (int) config('ai.limits.starter');

        DocumentImport::factory()->count($limit)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertFalse($this->planService->canImportDocument($user));
    }

    public function test_pro_user_is_never_limited(): void
    {
        $user = User::factory()->pro()->create();

        DocumentImport::factory()->count(999)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertTrue($this->planService->canImportDocument($user));
    }

    public function test_user_with_own_key_bypasses_limits(): void
    {
        $user = User::factory()->free()->withGeminiKey()->create();
        $limit = (int) config('ai.limits.free');

        DocumentImport::factory()->count($limit + 5)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertTrue($this->planService->canImportDocument($user));
    }

    // ------ PlanService: aiImportsRemainingToday ------

    public function test_remaining_decrements_with_each_import(): void
    {
        $user = User::factory()->free()->create();
        $limit = (int) config('ai.limits.free');

        DocumentImport::factory()->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertSame($limit - 1, $this->planService->aiImportsRemainingToday($user));
    }

    public function test_remaining_never_goes_below_zero(): void
    {
        $user = User::factory()->free()->create();
        $limit = (int) config('ai.limits.free');

        DocumentImport::factory()->count($limit + 10)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        $this->assertSame(0, $this->planService->aiImportsRemainingToday($user));
    }

    public function test_remaining_is_null_for_own_key_user(): void
    {
        $user = User::factory()->free()->withGeminiKey()->create();

        $this->assertNull($this->planService->aiImportsRemainingToday($user));
    }

    public function test_remaining_is_null_for_pro_user(): void
    {
        $user = User::factory()->pro()->create();

        $this->assertNull($this->planService->aiImportsRemainingToday($user));
    }

    // ------ DocumentImporter Livewire: computed properties ------

    public function test_livewire_can_import_is_true_below_limit(): void
    {
        $user = User::factory()->free()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\DocumentImporter::class)
            ->assertSet('canImport', true);
    }

    public function test_livewire_can_import_is_false_at_limit(): void
    {
        $user = User::factory()->free()->create();
        $limit = (int) config('ai.limits.free');

        DocumentImport::factory()->count($limit)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\DocumentImporter::class)
            ->assertSet('canImport', false);
    }

    public function test_livewire_ai_imports_limit_returns_null_for_pro(): void
    {
        $user = User::factory()->pro()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\DocumentImporter::class)
            ->assertSet('aiImportsLimit', null);
    }

    public function test_livewire_start_import_blocked_when_at_limit(): void
    {
        $user = User::factory()->free()->create();
        $limit = (int) config('ai.limits.free');

        DocumentImport::factory()->count($limit)->create([
            'user_id' => $user->id,
            'used_own_key' => false,
            'created_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\DocumentImporter::class)
            ->call('startImport')
            ->assertHasErrors('files');
    }
}
