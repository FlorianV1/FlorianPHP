<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Pages;

use App\Filament\Management\Resources\Clients\ClientResource;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

final class ManageClientActivities extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'activitiesAsSubject';

    protected static ?string $title = 'Activity';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getNavigationLabel(): string
    {
        return 'Activity';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('changes')
                    ->label('Changes')
                    ->state(function (Activity $record): string {
                        $attributes = $record->properties['attributes'] ?? [];

                        return collect($attributes)
                            ->map(fn ($value, string $key): string => "{$key}: ".(is_scalar($value) ? (string) $value : json_encode($value)))
                            ->implode(', ');
                    })
                    ->limit(80)
                    ->placeholder('—'),
                TextColumn::make('causer.name')
                    ->label('By')
                    ->placeholder('System'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25]);
    }
}
