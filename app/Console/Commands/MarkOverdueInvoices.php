<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Console\Command;

final class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Flag sent invoices past their due date as overdue';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->where('status', InvoiceStatus::Sent)
            ->whereDate('due_date', '<', today())
            ->get();

        // Individual updates so each transition lands in the activity log.
        $invoices->each(fn (Invoice $invoice) => $invoice->update(['status' => InvoiceStatus::Overdue]));

        $this->info("{$invoices->count()} invoice(s) marked overdue.");

        return self::SUCCESS;
    }
}
