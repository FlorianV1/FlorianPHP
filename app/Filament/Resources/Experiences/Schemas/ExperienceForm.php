<?php

namespace App\Filament\Resources\Experiences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Position Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Senior Software Engineer')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('company')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Acme Inc.'),

                                TextInput::make('company_url')
                                    ->url()
                                    ->placeholder('https://company.com'),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextInput::make('location')
                                    ->placeholder('Amsterdam, Netherlands'),

                                Select::make('employment_type')
                                    ->options([
                                        'full-time' => 'Full-time',
                                        'part-time' => 'Part-time',
                                        'contract' => 'Contract',
                                        'freelance' => 'Freelance',
                                        'internship' => 'Internship',
                                    ]),
                            ]),
                    ]),

                Section::make('Duration')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('started_at')
                                    ->label('Start Date')
                                    ->required(),

                                DatePicker::make('ended_at')
                                    ->label('End Date')
                                    ->disabled(fn ($get) => $get('is_current'))
                                    ->hidden(fn ($get) => $get('is_current')),

                                Toggle::make('is_current')
                                    ->label('Currently working here')
                                    ->default(false)
                                    ->live()
                                    ->inline(false),
                            ]),
                    ]),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief overview of your role...')
                            ->columnSpanFull(),

                        Repeater::make('responsibilities')
                            ->schema([
                                TextInput::make('responsibility')
                                    ->required()
                                    ->placeholder('Led development of...')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['responsibility'] ?? null)
                            ->addActionLabel('Add Responsibility')
                            ->columnSpanFull(),

                        TagsInput::make('skills')
                            ->placeholder('Add skills used...')
                            ->suggestions([
                                'PHP', 'Laravel', 'Vue.js', 'React', 'JavaScript',
                                'TypeScript', 'MySQL', 'PostgreSQL', 'Redis',
                                'Docker', 'AWS', 'Git', 'Tailwind CSS', 'Node.js',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Display Settings')
                    ->schema([
                        Grid::make(2)
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
