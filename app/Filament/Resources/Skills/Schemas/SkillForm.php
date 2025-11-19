<?php

namespace App\Filament\Resources\Skills\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SkillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Skill Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Laravel'),

                        TextInput::make('url')
                            ->url()
                            ->placeholder('https://laravel.com')
                            ->helperText('Link to official website'),

                        TextInput::make('icon')
                            ->placeholder('devicon-laravel-plain')
                            ->helperText('Devicon class - find icons at devicon.dev'),

                        Select::make('category')
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
                            ])
                            ->required()
                            ->default('other'),
                    ])
                    ->columns(2),

                Section::make('Display Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('order')
                                    ->numeric()
                                    ->default(0),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Show in marquee'),
                            ]),
                    ]),

                Section::make('Display Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('order')
                                    ->numeric()
                                    ->default(0),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
