<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Billing\IssueRetainerInvoice;
use App\Mail\RecurringInvoicesDrafted;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * The unattended half of retainer billing: every active retainer that has
 * opted in and whose period has started gets a draft invoice.
 *
 * Drafts, not sends. Nothing reaches a client until someone opens the invoice
 * and sends it — a wrong rate or a wrong period is a phone call to fix, so
 * the run buys you the typing, not the decision.
 */
final class IssueRecurringInvoices extends Command
{
    protected $signature = 'invoices:issue-recurring {--dry-run : List what would be billed and change nothing}';

    protected $description = 'Draft invoices for every retainer whose billing period has started';

    public function handle(IssueRetainerInvoice $issuer): int
    {
        $retainers = Retainer::query()
            ->dueForInvoicing()
            ->with(['client', 'website'])
            ->orderBy('next_due_date')
            ->get();

        if ($retainers->isEmpty()) {
            $this->info('Nothing due.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['Retainer', 'Client', 'Due', 'Amount'],
                $retainers->map(fn (Retainer $retainer): array => [
                    $retainer->description,
                    $retainer->client->company_name,
                    $retainer->next_due_date->toDateString(),
                    '€'.number_format((float) $retainer->amount, 2),
                ])->all(),
            );

            return self::SUCCESS;
        }

        /** @var list<Invoice> $drafted */
        $drafted = [];

        /** @var list<Retainer> $stalled */
        $stalled = [];

        foreach ($retainers as $retainer) {
            foreach ($issuer->catchUp($retainer) as $invoice) {
                $drafted[] = $invoice;
                $this->line("  {$invoice->number}  {$retainer->client->company_name}  €".number_format((float) $invoice->total, 2));
            }

            // Still due after a catch-up means it hit the period cap: too far
            // behind to bill without someone deciding what the client was
            // actually told. Reported rather than billed.
            if ($issuer->needsAttention($retainer)) {
                $stalled[] = $retainer;
                $this->warn("  {$retainer->description} for {$retainer->client->company_name} is more than ".IssueRetainerInvoice::MAX_CATCH_UP_PERIODS.' periods behind — left alone.');
            }
        }

        $this->info(count($drafted).' draft invoice(s) created.');

        $this->notify($drafted, $stalled);

        return self::SUCCESS;
    }

    /**
     * Mail the admins a summary. Drafts are invisible until someone opens the
     * panel, so without this the whole point of running unattended is lost.
     *
     * @param  list<Invoice>  $drafted
     * @param  list<Retainer>  $stalled
     */
    private function notify(array $drafted, array $stalled): void
    {
        if ($drafted === [] && $stalled === []) {
            return;
        }

        $recipients = User::query()->where('is_admin', true)->pluck('email')->all();

        if ($recipients === []) {
            $this->warn('No admin to notify.');

            return;
        }

        Mail::to($recipients)->send(new RecurringInvoicesDrafted($drafted, $stalled));
    }
}
