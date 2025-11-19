<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;

class ProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Overview')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Name')
                                            ->size(TextSize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-o-user'),

                                        Infolists\Components\TextEntry::make('role')
                                            ->label('Role')
                                            ->icon('heroicon-o-briefcase')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->copyMessage('Email copied!')
                                            ->copyMessageDuration(1500),
                                    ]),

                                    Group::make([
                                        Infolists\Components\IconEntry::make('status_available')
                                            ->label('Availability')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-x-circle')
                                            ->trueColor('success')
                                            ->falseColor('danger'),

                                        Infolists\Components\TextEntry::make('status_text')
                                            ->label('Status Message')
                                            ->badge()
                                            ->color(fn ($record) => $record->status_available ? 'success' : 'gray'),
                                    ]),
                                ]),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('Introduction')
                    ->schema([
                        Infolists\Components\TextEntry::make('tagline')
                            ->label('Tagline')
                            ->prose()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('subtitle')
                            ->label('Subtitle')
                            ->prose()
                            ->color('gray')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('About Me')
                    ->schema([
                        Infolists\Components\TextEntry::make('about_text')
                            ->label('')
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Social Links')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('social_links')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('platform')
                                    ->label('Platform')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-o-link'),

                                Infolists\Components\TextEntry::make('url')
                                    ->label('URL')
                                    ->url(fn ($state) => $state)
                                    ->openUrlInNewTab()
                                    ->copyable()
                                    ->copyMessage('URL copied!')
                                    ->color('primary'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->hidden(fn ($record) => empty($record->social_links)),

                Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-clock')
                            ->color('warning'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
