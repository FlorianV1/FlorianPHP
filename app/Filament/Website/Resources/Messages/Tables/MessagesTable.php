<?php

namespace App\Filament\Website\Resources\Messages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->icon(fn ($state) => $state ? 'heroicon-o-envelope-open' : 'heroicon-o-envelope')
                    ->color(fn ($state) => $state ? 'gray' : 'primary')
                    ->tooltip(fn ($state) => $state ? 'Read' : 'Unread')
                    ->width('40px'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(fn ($record) => $record->read_at ? null : 'bold'),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('message')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->message),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('Read')
                    ->nullable()
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),

                Filter::make('is_spam')
                    ->label('Show quarantined spam')
                    ->query(fn ($query) => $query->onlySpam())
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
