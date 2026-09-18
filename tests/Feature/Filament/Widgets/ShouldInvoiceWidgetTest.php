<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Enums\RetainerInterval;
use App\Filament\Management\Widgets\FinanceStatsWidget;
use App\Filament\Management\Widgets\RevenueChartWidget;
use App\Filament\Management\Widgets\ShouldInvoiceWidget;
use App\Filament\Management\Widgets\SiteHealthWidget;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\Website;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('renders the dashboard widgets', function (string $widget) {
    Website::factory()->create();
    Retainer::factory()->create();
    Invoice::factory()->create();

    livewire($widget)->assertOk();
})->with([
    FinanceStatsWidget::class,
    ShouldInvoiceWidget::class,
    RevenueChartWidget::class,
    SiteHealthWidget::class,
]);

it('creates a draft invoice from a due retainer and advances its due date', function () {
    $retainer = Retainer::factory()->create([
        'interval' => RetainerInterval::Monthly,
        'amount' => 150,
        'next_due_date' => now()->startOfMonth()->addDays(5),
    ]);

    $originalDueDate = $retainer->next_due_date;

    livewire(ShouldInvoiceWidget::class)
        ->callAction(TestAction::make('createDraft')->table($retainer))
        ->assertNotified();

    $invoice = Invoice::query()->latest('id')->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->client_id)->toBe($retainer->client_id)
        ->and((float) $invoice->subtotal)->toBe(150.0)
        ->and((float) $invoice->total)->toBe(181.5)
        ->and($retainer->refresh()->next_due_date->toDateString())
        ->toBe($originalDueDate->addMonth()->toDateString());
});
