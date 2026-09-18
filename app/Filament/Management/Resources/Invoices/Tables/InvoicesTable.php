<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.company_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn (Invoice $record): ?string => $record->isOutstanding() && $record->due_date->isPast() ? 'danger' : null),
                TextColumn::make('paid_at')
                    ->date()
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(InvoiceStatus::class),
                Filter::make('overdue')
                    ->label('Overdue')
                    // Mirrors Invoice::scopeOverdue(); inlined because the filter
                    // hands us a generic builder the scope can't be resolved on.
                    ->query(fn (Builder $query): Builder => $query
                        ->whereIn('status', InvoiceStatus::outstanding())
                        ->whereDate('due_date', '<', today())),
                SelectFilter::make('client')
                    ->relationship('client', 'company_name'),
            ])
            ->recordActions([
                Action::make('markSent')
                    ->label('Mark sent')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->visible(fn (Invoice $record): bool => $record->status === InvoiceStatus::Draft)
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->update(['status' => InvoiceStatus::Sent])),
                Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->visible(fn (Invoice $record): bool => $record->isOutstanding())
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->update([
                        'status' => InvoiceStatus::Paid,
                        'paid_at' => now(),
                    ])),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('downloadPdf')
                        ->label('Download PDF')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(fn (Invoice $record) => response()->streamDownload(
                            function () use ($record): void {
                                echo Pdf::loadView('invoices.pdf', ['invoice' => $record->load('lines', 'client', 'website')])->output();
                            },
                            "{$record->number}.pdf",
                        )),
                    Action::make('duplicate')
                        ->icon(Heroicon::OutlinedDocumentDuplicate)
                        ->action(function (Invoice $record) {
                            $copy = $record->replicate(['paid_at']);
                            $copy->number = Invoice::nextNumber();
                            $copy->status = InvoiceStatus::Draft;
                            $copy->issue_date = today();
                            $copy->due_date = today()->addDays(14);
                            $copy->paid_at = null;
                            $copy->save();

                            $record->lines()->get()->each(function ($line) use ($copy): void {
                                $copy->lines()->create($line->only(['description', 'quantity', 'unit_price', 'amount']));
                            });

                            $copy->recalculateTotals();

                            redirect(InvoiceResource::getUrl('edit', ['record' => $copy]));
                        }),
                    // TODO(next): accounting sync (Moneybird) — push via external_reference.
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
