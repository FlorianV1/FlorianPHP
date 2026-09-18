<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Pages;

use App\Filament\Management\Resources\Clients\ClientResource;
use App\Filament\Management\Resources\Retainers\RetainerResource;
use App\Models\Retainer;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ManageClientRetainers extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'retainers';

    protected static ?string $relatedResource = RetainerResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    public static function getNavigationLabel(): string
    {
        return 'Retainers';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('EUR'),
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
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
