<?php

declare(strict_types=1);

namespace App\Filament\Management\Widgets;

use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\Retainer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class ShouldInvoiceWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Should invoice this month')
            ->description('Active retainers due in the current period. Un-invoiced logged work has its own panel below.')
            ->query(
                Retainer::query()
                    ->where('active', true)
                    ->whereDate('next_due_date', '<=', now()->endOfMonth())
                    ->orderBy('next_due_date'),
            )
            ->columns([
                TextColumn::make('description'),
                TextColumn::make('client.company_name'),
                TextColumn::make('website.label')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->money('EUR'),
                TextColumn::make('interval')
                    ->badge(),
                TextColumn::make('next_due_date')
                    ->date()
                    ->color(fn (Retainer $record): ?string => $record->next_due_date->isPast() ? 'danger' : null),
            ])
            ->recordActions([
                Action::make('createDraft')
                    ->label('Create draft invoice')
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->requiresConfirmation()
                    ->modalDescription(fn (Retainer $record): string => "Creates a draft invoice of €{$record->amount} for {$record->client->company_name} and advances the retainer's next due date.")
                    ->action(function (Retainer $record): void {
                        $invoice = Invoice::openDraftFor($record->client, $record->website);

                        $invoice->lines()->create([
                            'description' => $record->description.' — '.$record->next_due_date->format('M Y'),
                            'quantity' => 1,
                            'unit_price' => $record->amount,
                            'amount' => $record->amount,
                        ]);

                        $invoice->recalculateTotals();

                        $record->update([
                            'next_due_date' => $record->next_due_date->addMonths($record->interval->months()),
                        ]);

                        Notification::make()
                            ->title("Draft {$invoice->number} created")
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
            ->emptyStateHeading('Nothing to invoice')
            ->emptyStateDescription('No active retainers are due this month.');
    }
}
