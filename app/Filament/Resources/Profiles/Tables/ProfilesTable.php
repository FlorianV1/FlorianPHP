<?php

namespace App\Filament\Resources\Profiles\Tables;

use Filament\Actions\CreateAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-o-user'),

                TextColumn::make('role')
                    ->label('Role')
                    ->sortable()
                    ->icon('heroicon-o-briefcase'),

                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500),

                IconColumn::make('status_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('status_text')
                    ->label('Status Message')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('social_links')
                    ->label('Social Links')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => count($state ?? []) . ' link(s)')
                    ->color('info'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->paginationPageOptions([false])
            ->emptyStateHeading('No profile yet')
            ->emptyStateDescription('Create your profile to get started with your portfolio.')
            ->emptyStateIcon('heroicon-o-user-circle')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Create Profile'),
            ]);
    }
}
