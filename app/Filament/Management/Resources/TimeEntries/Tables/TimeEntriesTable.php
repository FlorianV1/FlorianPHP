<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\TimeEntries\Tables;

use App\Models\TimeEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class TimeEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('client.company_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('website.label')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('hours')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('rate')
                    ->label('Rate')
                    ->state(fn (TimeEntry $record): float => $record->effectiveRate())
                    ->money('EUR'),
                TextColumn::make('amount')
                    ->state(fn (TimeEntry $record): float => $record->amount())
                    ->money('EUR'),
                IconColumn::make('invoiced')
                    ->state(fn (TimeEntry $record): bool => $record->isInvoiced())
                    ->boolean(),
            ])
            ->defaultSort('work_date', 'desc')
            ->filters([
                TernaryFilter::make('invoiced')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('invoice_line_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('invoice_line_id'),
                    ),
                SelectFilter::make('client')
                    ->relationship('client', 'company_name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (TimeEntry $record): bool => ! $record->isInvoiced()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
