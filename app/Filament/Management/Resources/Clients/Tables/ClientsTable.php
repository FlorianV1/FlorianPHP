<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Tables;

use App\Enums\ClientStatus;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('websites')
                ->withSum(
                    ['invoices as outstanding_total' => fn (Builder $q) => $q->whereIn('status', InvoiceStatus::outstanding())],
                    'total',
                )
                ->with(['retainers' => fn ($q) => $q->where('active', true)]))
            ->columns([
                TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('websites_count')
                    ->label('Websites')
                    ->sortable(),
                TextColumn::make('outstanding')
                    ->label('Outstanding')
                    ->state(fn (Client $record): float => (float) ($record->outstanding_total ?? 0))
                    ->money('EUR'),
                TextColumn::make('mrr')
                    ->label('MRR')
                    ->state(fn (Client $record): float => $record->monthlyRecurringRevenue())
                    ->money('EUR'),
                TextColumn::make('contact_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('onboarded_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('company_name')
            ->filters([
                SelectFilter::make('status')
                    ->options(ClientStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
