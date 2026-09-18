<?php

declare(strict_types=1);

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\Website;

it('stashes and restores the client status around a hard lock', function () {
    $client = Client::factory()->create(['status' => ClientStatus::Paused]);
    $website = Website::factory()->for($client)->create(['lock_level' => 3]);

    $client->syncLockStatus();

    expect($client->refresh()->status)->toBe(ClientStatus::Locked)
        ->and($client->status_before_lock)->toBe(ClientStatus::Paused);

    // Lifting the last hard lock restores the stashed status, not Active.
    $website->update(['lock_level' => 0]);
    $client->syncLockStatus();

    expect($client->refresh()->status)->toBe(ClientStatus::Paused)
        ->and($client->status_before_lock)->toBeNull();
});

it('falls back to Active when a locked client has no stashed status', function () {
    $client = Client::factory()->create([
        'status' => ClientStatus::Locked,
        'status_before_lock' => null,
    ]);
    Website::factory()->for($client)->create(['lock_level' => 0]);

    $client->syncLockStatus();

    expect($client->refresh()->status)->toBe(ClientStatus::Active);
});

it('normalizes active retainers to monthly recurring revenue', function () {
    $client = Client::factory()->create();
    Retainer::factory()->for($client)->create(['amount' => 100]);
    Retainer::factory()->for($client)->quarterly()->create(['amount' => 300]);
    Retainer::factory()->for($client)->yearly()->create(['amount' => 1200]);
    // Inactive retainers contribute nothing regardless of amount.
    Retainer::factory()->for($client)->create(['amount' => 500, 'active' => false]);

    expect($client->monthlyRecurringRevenue())->toBe(300.0);
});

it('counts only sent and overdue invoices in the outstanding balance', function () {
    $client = Client::factory()->create();
    Invoice::factory()->for($client)->sent()->create(['total' => 100]);
    Invoice::factory()->for($client)->overdue()->create(['total' => 250]);
    Invoice::factory()->for($client)->draft()->create(['total' => 40]);
    Invoice::factory()->for($client)->create(['total' => 999]); // paid

    expect($client->outstandingBalance())->toBe(350.0);
});
