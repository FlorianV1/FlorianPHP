<?php

declare(strict_types=1);

namespace App\Billing;

use App\Models\Invoice;
use App\Models\Retainer;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Turns one period of a retainer into a draft invoice.
 *
 * Extracted out of the dashboard button so the unattended run and the manual
 * click go down exactly one path — the two used to differ, and the button's
 * version advanced `next_due_date` with Carbon's overflowing `addMonths`.
 *
 * Billing is keyed to the period it covers, not to the retainer's current
 * state. That is what makes it safe to run on a schedule: a retry, an
 * overlapping cron, or a hand-click seconds after the cron all land on the
 * same `(retainer_id, period_start)`, and the second one is a no-op instead
 * of a second charge.
 */
final class IssueRetainerInvoice
{
    /**
     * How many periods one catch-up will bill. A retainer left dormant for a
     * year should not quietly emit twelve invoices to a client who was never
     * warned; past the cap the run stops and reports it so a human looks.
     */
    public const MAX_CATCH_UP_PERIODS = 3;

    /**
     * Draft the invoice for one period of a retainer and advance the retainer
     * past that period. Null means the period was already invoiced — the
     * schedule still moves on, because a period that is paid for is done
     * however it got there.
     */
    public function issue(Retainer $retainer, ?CarbonImmutable $periodStart = null): ?Invoice
    {
        $start = $periodStart ?? $retainer->next_due_date;

        try {
            $invoice = DB::transaction(function () use ($retainer, $start): ?Invoice {
                // Lock the retainer for the duration. Two concurrent runs
                // serialize here, so the duplicate check below is authoritative
                // rather than a race the unique index has to clean up after.
                $locked = Retainer::query()->whereKey($retainer->getKey())->lockForUpdate()->first();

                if ($locked === null) {
                    return null;
                }

                $invoice = $locked->invoices()->whereDate('period_start', $start)->exists()
                    ? null
                    : $this->draft($locked, $start);

                $this->advancePast($locked, $start);

                $retainer->setRawAttributes($locked->getAttributes(), true);

                return $invoice;
            });
        } catch (UniqueConstraintViolationException) {
            // The index had the last word — another runner took this period in
            // between. Not an error: the invoice exists, which was the point.
            return null;
        }

        return $invoice;
    }

    /**
     * Bill every period that has already started, so a week of downtime
     * catches up instead of silently skipping a month.
     *
     * @return list<Invoice>
     */
    public function catchUp(Retainer $retainer): array
    {
        $invoices = [];

        for ($period = 0; $period < self::MAX_CATCH_UP_PERIODS; $period++) {
            if (! $this->isDue($retainer)) {
                break;
            }

            $invoice = $this->issue($retainer);

            if ($invoice !== null) {
                $invoices[] = $invoice;
            }
        }

        return $invoices;
    }

    public function isDue(Retainer $retainer): bool
    {
        return ! $retainer->next_due_date->isAfter(today());
    }

    /**
     * True when there is still an un-billed period left after a catch-up hit
     * the cap — the signal that the run needs a human, not another cron tick.
     */
    public function needsAttention(Retainer $retainer): bool
    {
        return $this->isDue($retainer);
    }

    private function draft(Retainer $retainer, CarbonImmutable $start): Invoice
    {
        $end = $retainer->periodEndFrom($start);

        $invoice = Invoice::openDraftFor($retainer->client, $retainer->website, [
            'retainer_id' => $retainer->getKey(),
            'period_start' => $start,
            'period_end' => $end,
        ]);

        $invoice->lines()->create([
            'description' => $retainer->description.' — '.$this->periodLabel($start, $end),
            'quantity' => 1,
            'unit_price' => $retainer->amount,
            'amount' => $retainer->amount,
        ]);

        $invoice->recalculateTotals();

        return $invoice;
    }

    /**
     * Move the schedule to the period after the one just handled — never
     * backwards, so re-issuing an older period by hand cannot undo periods
     * that are already billed.
     */
    private function advancePast(Retainer $retainer, CarbonImmutable $start): void
    {
        $nextStart = $retainer->nextPeriodStartFrom($start);

        if ($nextStart->isAfter($retainer->next_due_date)) {
            $retainer->update(['next_due_date' => $nextStart]);
        }
    }

    /**
     * "Oct 2026" for a monthly period, "Oct 2026 – Dec 2026" for a longer one.
     */
    private function periodLabel(CarbonImmutable $start, CarbonImmutable $end): string
    {
        if ($start->isSameMonth($end)) {
            return $start->format('M Y');
        }

        return $start->format('M Y').' – '.$end->format('M Y');
    }
}
