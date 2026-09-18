<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Retainers\Tables;

use App\Enums\RetainerInterval;
use App\Models\Retainer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class RetainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('client.company_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('website.label')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('interval')
                    ->badge(),
                TextColumn::make('monthly')
                    ->label('Monthly equivalent')
                    ->state(fn (Retainer $record): float => $record->monthlyAmount())
                    ->money('EUR'),
                TextColumn::make('next_due_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('active')
                    ->boolean(),
            ])
            ->defaultSort('next_due_date')
            ->filters([
                TernaryFilter::make('active'),
                SelectFilter::make('interval')
                    ->options(RetainerInterval::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
