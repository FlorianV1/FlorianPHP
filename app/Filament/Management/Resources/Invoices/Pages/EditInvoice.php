<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Invoices\Pages;

use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->getRecord();
        $invoice->recalculateTotals();
    }
}
