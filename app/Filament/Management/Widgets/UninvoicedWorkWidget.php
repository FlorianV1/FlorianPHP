<?php

declare(strict_types=1);

namespace App\Filament\Management\Widgets;

use App\Billing\InvoiceFromWork;
use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Client;
use App\Models\TimeEntry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class UninvoicedWorkWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Un-invoiced logged work')
            ->query(
                Client::query()
                    ->whereHas('timeEntries', fn ($query) => $query->whereNull('invoice_line_id'))
                    ->withCount(['timeEntries as uninvoiced_entries_count' => fn ($query) => $query->whereNull('invoice_line_id')])
                    ->with(['timeEntries' => fn ($query) => $query->whereNull('invoice_line_id')])
                    ->orderBy('company_name'),
            )
            ->columns([
                TextColumn::make('company_name'),
                TextColumn::make('uninvoiced_entries_count')
                    ->label('Entries'),
                TextColumn::make('uninvoiced_hours')
                    ->label('Hours')
                    ->state(fn (Client $record): float => (float) $record->timeEntries->sum(
                        fn (TimeEntry $entry): float => (float) $entry->hours,
                    )),
                TextColumn::make('uninvoiced_amount')
                    ->label('Amount')
                    // Entries are eager-loaded; point each at the parent client so
                    // effectiveRate() doesn't re-query it per row.
                    ->state(fn (Client $record): float => round($record->timeEntries->sum(
                        fn (TimeEntry $entry): float => $entry->setRelation('client', $record)->amount(),
                    ), 2))
                    ->money('EUR'),
            ])
            ->recordActions([
                Action::make('invoiceWork')
                    ->label('Create draft invoice')
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->requiresConfirmation()
                    ->modalDescription(fn (Client $record): string => "Bundles all un-invoiced entries for {$record->company_name} into one draft invoice, one line per entry.")
                    ->action(function (Client $record): void {
                        $invoice = app(InvoiceFromWork::class)->create($record);

                        if ($invoice === null) {
                            Notification::make()->title('No un-invoiced work found')->warning()->send();

                            return;
                        }

                        Notification::make()
                            ->title("Draft {$invoice->number} created from logged work")
                            ->actions([
                                Action::make('edit')
                                    ->label('Open invoice')
                                    ->url(InvoiceResource::getUrl('edit', ['record' => $invoice])),
                            ])
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('All logged work is invoiced');
    }
}
