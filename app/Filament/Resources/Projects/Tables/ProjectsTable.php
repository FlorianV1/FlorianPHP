<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
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
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width(50),

                ToggleColumn::make('is_posted')
                    ->label('Posted')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Project Title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->wrap(),

                TextColumn::make('role')
                    ->label('Role')
                    ->sortable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable()
                    ->wrap()
                    ->toggleable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('tech_stack')
                    ->label('Tech Stack')
                    ->badge()
                    ->separator(',')
                    ->limit(3)
                    ->searchable()
                    ->wrap(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('live_url')
                    ->label('Live')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->live_url)
                    ->openUrlInNewTab()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ? 'Demo' : '-')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')

            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Featured Projects')
                    ->placeholder('All projects')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),

                TernaryFilter::make('is_posted')
                    ->label('Active Status')
                    ->placeholder('All projects')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

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
            ]);
    }
}
