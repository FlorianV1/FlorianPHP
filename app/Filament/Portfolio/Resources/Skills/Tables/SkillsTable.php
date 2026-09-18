<?php

namespace App\Filament\Portfolio\Resources\Skills\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SkillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn ($state) => $state ? '✓' : '—')
                    ->tooltip(fn ($record) => $record->icon),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'languages' => 'primary',
                        'frontend' => 'info',
                        'backend' => 'success',
                        'database' => 'warning',
                        'devops' => 'danger',
                        'tools' => 'gray',
                        'cms' => 'purple',
                        'testing' => 'orange',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('url')
                    ->label('Website')
                    ->limit(25)
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->placeholder('—'),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'languages' => 'Languages',
                        'frontend' => 'Frontend',
                        'backend' => 'Backend',
                        'database' => 'Database',
                        'devops' => 'DevOps',
                        'tools' => 'Tools',
                        'cms' => 'CMS',
                        'testing' => 'Testing',
                        'other' => 'Other',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('order')
            ->reorderable('order')
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
