<?php

declare(strict_types=1);

namespace App\Billing;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\DB;

/**
 * Bundles a client's un-invoiced time entries into a draft invoice, one
 * line per entry, and links the entries so they can't be billed twice.
 */
final class InvoiceFromWork
{
    public function create(Client $client): ?Invoice
    {
        // Lock the entries for the duration so two concurrent runs can't both
        // scoop up the same work and double-bill it — the second run waits,
        // then finds nothing left to invoice.
        return DB::transaction(function () use ($client): ?Invoice {
            $entries = $client->timeEntries()
                ->uninvoiced()
                ->orderBy('work_date')
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                return null;
            }

            $invoice = Invoice::openDraftFor($client);

            $entries->each(function (TimeEntry $entry) use ($invoice): void {
                $line = $invoice->lines()->create([
                    'description' => $entry->work_date->format('d M').' — '.$entry->description,
                    'quantity' => $entry->hours,
                    'unit_price' => $entry->effectiveRate(),
                    'amount' => $entry->amount(),
                ]);

                $entry->update(['invoice_line_id' => $line->id]);
            });

            $invoice->recalculateTotals();

            return $invoice;
        });
    }
}
