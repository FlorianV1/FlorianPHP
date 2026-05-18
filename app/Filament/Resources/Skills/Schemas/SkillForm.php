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
                Section::make('Skill')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Laravel'),

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
                            ->default('other')
                            ->native(false),

                        FileUpload::make('logo')
                            ->label('Custom logo (optional)')
                            ->image()
                            ->directory('skill-logos')
                            ->nullable()
                            ->helperText('Takes priority over devicon icon')
                            ->columnSpanFull(),

                        TextInput::make('icon')
                            ->placeholder('devicon-laravel-plain')
                            ->helperText('Find icons at devicon.dev — used if no logo uploaded')
                            ->columnSpanFull(),

                        TextInput::make('url')
                            ->url()
                            ->placeholder('https://laravel.com')
                            ->helperText('Official website')
                            ->columnSpanFull(),
                    ]),

                Section::make('Display')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Show in marquee')
                            ->default(true)
                            ->inline(false),

                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower = shown first'),
                    ]),
            ]);
    }
}
