<?php

use App\Filament\Management\Widgets\LeadsTrendChart;
use App\Filament\Portfolio\Widgets\PageViewsChart;
use App\Models\ContactMessage;
use App\Models\PageView;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

/**
 * The dashboard charts bucket counts into fixed periods and let the reader
 * pick the span. They deliberately carry no colours of their own — Filament
 * resolves those from the panel theme and re-resolves them in dark mode — and
 * they deliberately do not poll.
 */
beforeEach(function (): void {
    $this->actingAs(User::factory()->admin()->create());
});

/**
 * `getData()` is protected, as Filament intends; bind a closure to read it
 * rather than asserting against the rendered canvas markup.
 *
 * @return array{datasets: array<int, array<string, mixed>>, labels: array<int, string>}
 */
function chartData(object $widget): array
{
    return (fn (): array => $this->getData())->call($widget);
}

it('buckets leads by week over the selected span', function () {
    // Two in the current week, one three weeks back, one outside every span.
    ContactMessage::create(['name' => 'A', 'email' => 'a@example.com', 'message' => 'Hi']);
    ContactMessage::create(['name' => 'B', 'email' => 'b@example.com', 'message' => 'Hi']);

    ContactMessage::create(['name' => 'C', 'email' => 'c@example.com', 'message' => 'Hi'])
        ->forceFill(['created_at' => CarbonImmutable::now()->startOfWeek()->subWeeks(3)->addDay()])
        ->save();

    ContactMessage::create(['name' => 'D', 'email' => 'd@example.com', 'message' => 'Hi'])
        ->forceFill(['created_at' => CarbonImmutable::now()->subYears(2)])
        ->save();

    $data = chartData(Livewire::test(LeadsTrendChart::class)->instance());
    $counts = $data['datasets'][0]['data'];

    expect($counts)->toHaveCount(12)
        ->and($data['labels'])->toHaveCount(12)
        // Buckets run oldest first, so the current week is last.
        ->and(end($counts))->toBe(2)
        ->and($counts[12 - 4])->toBe(1)
        // The two-year-old message falls outside the window entirely.
        ->and(array_sum($counts))->toBe(3);
});

it('widens the lead buckets when the filter changes', function () {
    $widget = Livewire::test(LeadsTrendChart::class)->set('filter', '26')->instance();

    expect(chartData($widget)['labels'])->toHaveCount(26);
});

it('buckets page views by day over the selected span', function () {
    PageView::create(['page' => '/', 'ip' => '127.0.0.1']);
    PageView::create(['page' => '/about', 'ip' => '127.0.0.1']);

    PageView::create(['page' => '/', 'ip' => '127.0.0.1'])
        ->forceFill(['created_at' => now()->subDays(3)->setTime(12, 0)])
        ->save();

    $data = chartData(Livewire::test(PageViewsChart::class)->instance());
    $counts = $data['datasets'][0]['data'];

    expect($counts)->toHaveCount(30)
        ->and(end($counts))->toBe(2)
        ->and($counts[30 - 4])->toBe(1)
        ->and(array_sum($counts))->toBe(3);
});

it('narrows the page view buckets when the filter changes', function () {
    $widget = Livewire::test(PageViewsChart::class)->set('filter', '7')->instance();

    expect(chartData($widget)['labels'])->toHaveCount(7);
});

it('leaves chart colours to the panel theme', function (string $chart) {
    $dataset = chartData(Livewire::test($chart)->instance())['datasets'][0];

    // Filament reads the panel's colour off sentinel elements and re-reads it
    // when the theme flips. A colour on the dataset would override all of it.
    expect($dataset)->not->toHaveKey('borderColor')
        ->and($dataset)->not->toHaveKey('backgroundColor');
})->with([
    'leads' => [LeadsTrendChart::class],
    'page views' => [PageViewsChart::class],
]);

it('does not poll the dashboard charts', function (string $chart) {
    $interval = (fn (): ?string => $this->getPollingInterval())
        ->call(Livewire::test($chart)->instance());

    expect($interval)->toBeNull();
})->with([
    'leads' => [LeadsTrendChart::class],
    'page views' => [PageViewsChart::class],
]);

it('renders both charts as bars', function (string $chart) {
    $type = (fn (): string => $this->getType())->call(Livewire::test($chart)->instance());

    expect($type)->toBe('bar');
})->with([
    'leads' => [LeadsTrendChart::class],
    'page views' => [PageViewsChart::class],
]);
