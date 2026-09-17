<?php

namespace App\Filament\Website\Resources\Experiences\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columns(2)
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Select::make('entry_type')
                            ->label('Entry type')
                            ->options([
                                'work' => 'Work',
                                'education' => 'Education',
                            ])
                            ->default('work')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText('Education entries render in their own section on the site.')
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(fn ($get) => $get('entry_type') === 'education'
                                ? 'BSc Computer Science'
                                : 'Senior Software Engineer')
                            ->columnSpanFull(),

                        TextInput::make('company')
                            ->required()
                            ->maxLength(255)
                            ->label(fn ($get) => $get('entry_type') === 'education' ? 'School / institution' : 'Company')
                            ->placeholder(fn ($get) => $get('entry_type') === 'education' ? 'Avans Hogeschool' : 'Acme Inc.')
                            ->prefixIcon('heroicon-o-building-office-2'),

                        TextInput::make('credential')
                            ->label('Credential / diploma')
                            ->maxLength(255)
                            ->placeholder('Bachelor of Science')
                            ->visible(fn ($get) => $get('entry_type') === 'education')
                            ->columnSpanFull(),

                        TextInput::make('company_url')
                            ->url()
                            ->label('Company website')
                            ->placeholder('https://company.com')
                            ->prefixIcon('heroicon-o-globe-alt'),

                        FileUpload::make('company_logo')
                            ->label('Company logo')
                            ->image()
                            ->imageEditor()
                            ->directory('company-logos')
                            ->nullable()
                            ->helperText('PNG or SVG, shown in the timeline'),

                        TextInput::make('location')
                            ->placeholder('Amsterdam, Netherlands')
                            ->prefixIcon('heroicon-o-map-pin'),

                        Select::make('employment_type')
                            ->label('Employment type')
                            ->options([
                                'full-time'  => 'Full-time',
                                'part-time'  => 'Part-time',
                                'contract'   => 'Contract',
                                'freelance'  => 'Freelance',
                                'internship' => 'Internship',
                            ])
                            ->native(false)
                            ->prefixIcon('heroicon-o-tag'),
                    ])->columnSpanFull(),
                    Section::make('Timeline')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            Toggle::make('is_current')
                                ->label('Currently working here')
                                ->default(false)
                                ->live()
                                ->inline(false),
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('started_at')
                                        ->label('Start date')
                                        ->required()
                                        ->native(false),

                                    DatePicker::make('ended_at')
                                        ->label('End date')
                                        ->native(false)
                                        ->hidden(fn ($get) => $get('is_current')),
                                ]),
                        ]),

                    Section::make('Visibility')
                        ->icon('heroicon-o-eye')
                        ->headerActions([
                            Action::make('info')
                                ->label(false)
                                ->icon('heroicon-o-information-circle')
                                ->iconButton()
                                ->action(function () {
                                    Notification::make()
                                        ->title('Lower numbers appear first')
                                        ->body('So if you like it lower the number :)')
                                        ->info()
                                        ->send();
                                })
                        ])
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Show on portfolio')
                                ->default(true)
                                ->inline(false),

                            TextInput::make('order')
                                ->numeric()
                                ->default(0),
                        ]),

                Section::make('Overview')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(2)
                    ->schema([
                        Textarea::make('description')
                            ->label('')
                            ->rows(6)
                            ->placeholder('Describe your role, the team context, and the impact you had...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Skills used')
                    ->icon('heroicon-o-cpu-chip')
                    ->description('Tech & tools')
                    ->columnSpan(1)
                    ->schema([
                        TagsInput::make('skills')
                            ->label('')
                            ->placeholder('Type and press Enter...')
                            ->suggestions([
                                'PHP', 'Laravel', 'Vue.js', 'React', 'JavaScript',
                                'TypeScript', 'MySQL', 'PostgreSQL', 'Redis',
                                'Docker', 'AWS', 'Git', 'Tailwind CSS', 'Node.js',
                                'Figma', 'Linux', 'GraphQL', 'REST API',
                            ]),
                    ]),

            Section::make('Responsibilities')
                ->icon('heroicon-o-list-bullet')
                ->description('Key things you owned or delivered in this role')
                ->schema([
                    Repeater::make('responsibilities')
                        ->label('')
                        ->schema([
                            TextInput::make('responsibility')
                                ->required()
                                ->placeholder('Built and maintained a REST API serving 50k daily requests')
                                ->columnSpanFull(),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['responsibility'] ?? null)
                        ->addActionLabel('Add responsibility')
                        ->reorderable()
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
