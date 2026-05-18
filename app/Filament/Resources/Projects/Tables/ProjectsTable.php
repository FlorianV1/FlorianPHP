<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record) => $record->role ?: $record->project_type)
                    ->wrap(),

                TextColumn::make('tech_stack')
                    ->label('Stack')
                    ->badge()
                    ->separator(',')
                    ->wrap(),

                TextColumn::make('started_at')
                    ->label('Year')
                    ->date('Y')
                    ->color('gray'),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter(),

                ToggleColumn::make('is_posted')
                    ->label('Live'),

                TextColumn::make('live_url')
                    ->label('Link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->live_url)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => $state ? 'View' : '—')
                    ->alignCenter(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),

                TernaryFilter::make('is_posted')
                    ->label('Live')
                    ->placeholder('All')
                    ->trueLabel('Live only')
                    ->falseLabel('Hidden only'),

                SelectFilter::make('tech_stack')
                    ->label('Technology')
                    ->multiple()
                    ->options(function () {
                        return Project::query()
                            ->get()
                            ->pluck('tech_stack')
                            ->flatten()
                            ->unique()
                            ->sort()
                            ->mapWithKeys(fn ($tech) => [$tech => $tech]);
                    })
                    ->searchable(),
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
