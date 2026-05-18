<?php

namespace App\Filament\Resources\Experiences\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExperiencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => $record->company),

                TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'full-time' => 'success',
                        'part-time' => 'info',
                        'contract' => 'warning',
                        'freelance' => 'purple',
                        'internship' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('period_label')
                    ->label('Period')
                    ->getStateUsing(fn($record) => $record->period_label)
                    ->description(fn($record) => $record->duration)
                    ->color('gray'),

                IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('')
                    ->trueColor('success')
                    ->alignCenter(),

                ToggleColumn::make('is_active')
                    ->label('Visible'),

                TextColumn::make('order')
                    ->sortable()
                    ->alignCenter()
                    ->width(60),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                SelectFilter::make('employment_type')
                    ->options([
                        'full-time' => 'Full-time',
                        'part-time' => 'Part-time',
                        'contract' => 'Contract',
                        'freelance' => 'Freelance',
                        'internship' => 'Internship',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Visible'),
                TernaryFilter::make('is_current')
                    ->label('Current position'),
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
