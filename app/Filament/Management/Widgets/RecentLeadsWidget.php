<?php

namespace App\Filament\Management\Widgets;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentLeadsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest enquiries')
            ->description('Spam is quarantined and never shown here.')
            ->query(fn (): Builder => ContactMessage::query()->latest())
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('Messages sent through the contact form on the public site land here.')
            ->emptyStateIcon('heroicon-o-inbox')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (ContactMessage $record): string => $record->email),

                TextColumn::make('message')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (ContactMessage $record): string => $record->created_at->toDayDateTimeString()),

                TextColumn::make('read_at')
                    ->label('Status')
                    ->badge()
                    ->state(fn (ContactMessage $record): string => $record->isRead() ? 'Read' : 'Unread')
                    ->color(fn (ContactMessage $record): string => $record->isRead() ? 'gray' : 'warning'),
            ])
            ->recordActions([
                // The inbox itself lives in the Website panel, so this crosses
                // panels deliberately rather than duplicating the resource.
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (ContactMessage $record): string => route(
                        'filament.website.resources.messages.view',
                        ['record' => $record],
                    )),
            ]);
    }
}
