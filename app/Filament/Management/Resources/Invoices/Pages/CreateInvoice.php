<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Invoices\Pages;

use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;

final class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterCreate(): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->getRecord();
        $invoice->recalculateTotals();
    }
}
