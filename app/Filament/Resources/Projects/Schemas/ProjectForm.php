<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Wizard::make([
                            Step::make('Project')
                                ->icon('heroicon-o-document-text')
                                ->schema([
                                    Section::make('Project Details')
                                        ->schema([
                                            TextInput::make('title')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Deployment Dashboard')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                    if ($state !== null) {
                                                        $set('title', ucfirst($state));
                                                    }
                                                })
                                                ->columnSpanFull(),

                                            Textarea::make('description')
                                                ->required()
                                                ->rows(3)
                                                ->columnSpanFull(),

                                            Textarea::make('impact')
                                                ->required()
                                                ->rows(2)
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(1),

                                    Section::make('Role & Timeline')
                                        ->schema([
                                            Grid::make(3)
                                                ->schema([
                                                    TextInput::make('role')
                                                        ->maxLength(255),

                                                    Select::make('project_type')
                                                        ->options([
                                                            'client'      => 'Client project',
                                                            'internal'    => 'Internal tool',
                                                            'open_source' => 'Open source',
                                                            'product'     => 'Product / SaaS',
                                                        ])
                                                        ->native(false),
                                                ]),

                                            Grid::make(3)
                                                ->schema([
                                                    DatePicker::make('started_at')
                                                        ->label('Started At')
                                                        ->required(),

                                                    DatePicker::make('finished_at')
                                                        ->label('Finished At')
                                                        ->helperText('Leave empty if ongoing')
                                                        ->disabled(fn ($get) => $get('is_ongoing'))
                                                        ->hidden(fn ($get) => $get('is_ongoing')),

                                                    Toggle::make('is_ongoing')
                                                        ->label('Ongoing Project')
                                                        ->default(false)
                                                        ->inline(false),
                                                ]),

                                            Textarea::make('responsibilities')
                                                ->rows(3)
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(1),
                                ])
                                ->columns(1)
                                ->columnSpanFull(),

                            Step::make('Tech & Branding')
                                ->icon('heroicon-o-cpu-chip')
                                ->schema([
                                    Section::make('Technology Stack')
                                        ->schema([
                                            Select::make('languages')
                                                ->multiple()
                                                ->searchable()
                                                ->options([
                                                    'php'        => 'PHP',
                                                    'laravel'    => 'Laravel',
                                                    'javascript' => 'JavaScript',
                                                    'typescript' => 'TypeScript',
                                                    'vue'        => 'Vue',
                                                    'react'      => 'React',
                                                    'tailwind'   => 'Tailwind CSS',
                                                    'alpine'     => 'Alpine.js',
                                                    'mysql'      => 'MySQL',
                                                    'postgres'   => 'PostgreSQL',
                                                    'redis'      => 'Redis',
                                                    'docker'     => 'Docker',
                                                    'node'       => 'Node.js',
                                                    'aws'        => 'AWS',
                                                ])
                                                ->preload()
                                                ->columnSpanFull(),

                                            TagsInput::make('tech_stack')
                                                ->placeholder('Type and press Enter…')
                                                ->suggestions([
                                                    'Laravel',
                                                    'Symfony',
                                                    'Vue',
                                                    'React',
                                                    'Next.js',
                                                    'Nuxt',
                                                    'MySQL',
                                                    'PostgreSQL',
                                                    'Redis',
                                                    'Docker',
                                                    'Tailwind CSS',
                                                    'Alpine.js',
                                                    'Node.js',
                                                    'AWS',
                                                ])
                                                ->splitKeys(['Tab', ','])
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(1),

                                    Section::make('Branding')
                                        ->schema([
                                            FileUpload::make('logo')
                                                ->image()
                                                ->directory('projects/logos')
                                                ->maxSize(2048),
                                        ])
                                        ->columns(1),
                                ])
                                ->columnSpanFull(),

                            Step::make('Links & Visibility')
                                ->icon('heroicon-o-link')
                                ->schema([
                                    Section::make('Project Links')
                                        ->schema([
                                            TextInput::make('code_url')
                                                ->url(),

                                            TextInput::make('live_url')
                                                ->url(),
                                        ])
                                        ->columns(2),

                                    Section::make('Display Settings')
                                        ->schema([
                                            Grid::make(4)
                                            ->schema([
                                                TextInput::make('order')
                                                    ->numeric()
                                                    ->default(0),
                                                Toggle::make('is_featured')
                                                    ->default(false),
                                                Toggle::make('is_posted')
                                                    ->label('Posted')
                                                    ->default(false),
                                            ]),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ])
                            ->skippable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
