<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Your primary profile information displayed on the site')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Florian')
                            ->helperText('Your preferred public name.'),

                        TextInput::make('role')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Software Developer')
                            ->helperText('Your current primary professional title.'),

                        Textarea::make('tagline')
                            ->required()
                            ->rows(2)
                            ->placeholder('I build web applications with a focus on reliability, performance, and clear code.')
                            ->helperText('A short, compelling summary or main pitch.')
                            ->columnSpanFull(),

                        Textarea::make('subtitle')
                            ->required()
                            ->rows(2)
                            ->placeholder('Currently working on modern PHP/Laravel stacks...')
                            ->helperText('Brief secondary context or a list of current technologies.')
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('dev@example.com')
                            ->helperText('The public email address for contact.'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Availability Status')
                    ->description('Let visitors know if you\'re available for work')
                    ->schema([
                        Toggle::make('status_available')
                            ->label('Available for work')
                            ->helperText('Indicates your general availability to clients or recruiters.')
                            ->default(true)
                            ->inline(false)
                            ->live(),

                        TextInput::make('status_text')
                            ->label('Status message')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Available for freelance opportunities')
                            ->helperText('A short, specific message regarding your availability status.'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('About Section')
                    ->description('Tell your story - keep it personal but professional')
                    ->schema([
                        RichEditor::make('about_text')
                            ->required()
                            ->label('About me text')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                            ])
                            ->placeholder('Write 2-3 paragraphs about yourself, your approach to work, and interests...')
                            ->helperText('Detailed professional biography. Use formatting for better readability.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Social Links')
                    ->description('Add your social media and professional profiles')
                    ->schema([
                        Repeater::make('social_links')
                            ->schema([
                                TextInput::make('platform')
                                    ->required()
                                    ->placeholder('GitHub')
                                    ->helperText('e.g., GitHub, LinkedIn, Discord'),

                                TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://github.com/yourusername')
                                    ->helperText('Full URL including the protocol (https://)'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add social link')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['platform'] ?? null)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
