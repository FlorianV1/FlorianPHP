<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Websites\Tables;

use App\Enums\WebsiteEnvironment;
use App\Models\Website;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Website $record): string => $record->url),
                TextColumn::make('client.company_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('environment')
                    ->badge(),
                TextColumn::make('lock_level')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Unlocked' : "Locked (L{$state})"),
                TextColumn::make('last_seen_at')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),
                TextColumn::make('hosting_provider')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('label')
            ->filters([
                SelectFilter::make('environment')
                    ->options(WebsiteEnvironment::class),
                SelectFilter::make('client')
                    ->relationship('client', 'company_name'),
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
