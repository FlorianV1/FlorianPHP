<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\TimeEntries\Schemas;

use App\Models\Website;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class TimeEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('client_id')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Select::make('website_id')
                            ->label('Website')
                            ->options(fn (Get $get): array => Website::query()
                                ->where('client_id', $get('client_id'))
                                ->pluck('label', 'id')
                                ->all())
                            ->placeholder('— none —'),
                        DatePicker::make('work_date')
                            ->default(today())
                            ->required(),
                        TextInput::make('hours')
                            ->numeric()
                            ->minValue(0.25)
                            ->step(0.25)
                            ->required(),
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('hourly_rate')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('Client default')
                            ->helperText('Leave empty to bill at the client\'s hourly rate.'),
                    ]),
            ]);
    }
}
