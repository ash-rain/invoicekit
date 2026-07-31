<?php

namespace Tests\Feature\Filament;

use App\Filament\Widgets\MonthComparisonChart;
use App\Filament\Widgets\SalesPerDayChart;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private function getChartData(object $widget): array
    {
        $method = new ReflectionMethod($widget, 'getData');
        $method->setAccessible(true);

        return $method->invoke($widget);
    }

    public function test_sales_per_day_chart_includes_invoice_dataset(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        Invoice::factory()->count(2)->for($user)->for($client)->create(['created_at' => now()]);

        $this->actingAs($admin, 'admin');

        $widget = Livewire::test(SalesPerDayChart::class)->instance();
        $data = $this->getChartData($widget);

        $invoiceDataset = collect($data['datasets'])->firstWhere('label', 'New Invoices');

        $this->assertNotNull($invoiceDataset);
        $this->assertSame(2, array_sum($invoiceDataset['data']));
    }

    public function test_month_comparison_chart_includes_invoice_datasets(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        Invoice::factory()->count(2)->for($user)->for($client)->create(['created_at' => now()]);

        $this->actingAs($admin, 'admin');

        $widget = Livewire::test(MonthComparisonChart::class)->instance();
        $data = $this->getChartData($widget);

        $labels = collect($data['datasets'])->pluck('label');

        $this->assertTrue($labels->contains(fn (string $label) => str_contains($label, 'Invoices')));

        $currentMonthInvoices = collect($data['datasets'])
            ->first(fn (array $dataset) => str_contains($dataset['label'], now()->format('F Y')) && str_contains($dataset['label'], 'Invoices'));

        $this->assertSame(2, array_sum($currentMonthInvoices['data']));
    }
}
