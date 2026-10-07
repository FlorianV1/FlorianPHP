<?php

declare(strict_types=1);

namespace App\Filament\Management\Widgets;

use App\Billing\IssueRetainerInvoice;
use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Retainer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
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
            ->description('Active retainers due in the current period. Ones marked for automatic billing are drafted by the nightly run; the rest wait for the button. Un-invoiced logged work has its own panel below.')
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
                IconColumn::make('auto_invoice')
                    ->label('Auto')
                    ->boolean()
                    ->tooltip(fn (Retainer $record): string => $record->auto_invoice
                        ? 'Drafted automatically by the nightly run'
                        : 'Billed only when you press the button'),
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
                    // Same service the scheduled run uses, so clicking this
                    // the morning after the cron already billed the period is
                    // a no-op rather than a second charge.
                    ->action(function (Retainer $record, IssueRetainerInvoice $issuer): void {
                        $invoice = $issuer->issue($record);

                        if ($invoice === null) {
                            Notification::make()
                                ->title('Already invoiced')
                                ->body("{$record->description} is paid up to ".$record->next_due_date->format('d M Y').'.')
                                ->warning()
                                ->send();

                            return;
                        }

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
