<?php

declare(strict_types=1);

use App\Enums\RetainerInterval;

it('normalizes an interval amount to its monthly equivalent', function (RetainerInterval $interval, float $amount, float $expected) {
    expect($interval->monthlyAmount($amount))->toBe($expected);
})->with([
    'monthly' => [RetainerInterval::Monthly, 95.0, 95.0],
    'quarterly' => [RetainerInterval::Quarterly, 300.0, 100.0],
    'yearly' => [RetainerInterval::Yearly, 1200.0, 100.0],
    'rounds to cents' => [RetainerInterval::Quarterly, 100.0, 33.33],
]);
