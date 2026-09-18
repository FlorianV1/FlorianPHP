<?php

declare(strict_types=1);

namespace App\Filament\Management\Widgets;

use App\Filament\Management\Resources\Websites\WebsiteResource;
use App\Models\Website;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class SiteHealthWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Site health')
            ->query(Website::query()->with('client')->orderBy('label'))
            ->columns([
                TextColumn::make('health_state')
                    ->label('')
                    ->state(fn (Website $record): string => ucfirst($record->healthStatus()))
                    ->badge()
                    ->color(fn (Website $record): string => $record->healthStatusColor()),
                TextColumn::make('label')
                    ->description(fn (Website $record): string => $record->client->company_name),
                TextColumn::make('environment')
                    ->badge(),
                TextColumn::make('last_seen_at')
                    ->label('Last seen')
                    ->since()
                    ->placeholder('Never')
                    ->description(fn (Website $record): ?string => $record->isEnrolled() && $record->heartbeatIsStale()
                        ? 'No recent check-in'
                        : null)
                    ->color(fn (Website $record): ?string => $record->isEnrolled() && $record->heartbeatIsStale()
                        ? 'danger'
                        : null),
                TextColumn::make('errors')
                    ->label('Errors')
                    ->state(fn (Website $record): int => $record->healthErrorCount())
                    ->color(fn (Website $record): ?string => $record->healthErrorCount() > 0 ? 'danger' : null),
                TextColumn::make('updates')
                    ->label('Pending updates')
                    ->state(fn (Website $record): ?int => $record->healthPendingUpdates())
                    ->placeholder('—'),
                TextColumn::make('lock_level')
                    ->label('Lock')
                    ->badge()
                    ->color(fn (Website $record): string => $record->lockBadgeColor())
                    ->formatStateUsing(fn (int $state, Website $record): string => $record->lockChangePending()
                        ? "L{$state} → L{$record->desired_lock_level}"
                        : "L{$state}"),
            ])
            ->recordUrl(fn (Website $record): string => WebsiteResource::getUrl('view', ['record' => $record]))
            ->poll('120s')
            ->paginated([10, 25]);
    }
}
