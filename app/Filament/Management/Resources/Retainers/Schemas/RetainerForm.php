<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Retainers\Schemas;

use App\Enums\RetainerInterval;
use App\Models\Website;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class RetainerForm
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
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('amount')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Select::make('interval')
                            ->options(RetainerInterval::class)
                            ->default(RetainerInterval::Monthly)
                            ->required(),
                        DatePicker::make('next_due_date')
                            ->required(),
                        Toggle::make('active')
                            ->default(true),
                    ]),
            ]);
    }
}
