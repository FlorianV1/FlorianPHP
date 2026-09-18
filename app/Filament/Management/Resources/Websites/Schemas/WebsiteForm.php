<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Websites\Schemas;

use App\Enums\WebsiteEnvironment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class WebsiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('client_id')
                                ->relationship('client', 'company_name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('label')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('url')
                                ->label('Primary URL')
                                ->url()
                                ->required()
                                ->maxLength(255),
                            Select::make('environment')
                                ->options(WebsiteEnvironment::class)
                                ->default(WebsiteEnvironment::Production)
                                ->required(),
                            Textarea::make('tech_stack_notes')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),
                Section::make('Hosting & repository')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('hosting_provider')
                                ->maxLength(255),
                            TextInput::make('server_host')
                                ->label('Server IP / hostname')
                                ->maxLength(255),
                            TextInput::make('repository_url')
                                ->url()
                                ->maxLength(255),
                        ]),
                    ]),
                Section::make('Integrations')
                    ->description('Quick links shown on the website page.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('management_url')
                                ->label('Management / admin URL')
                                ->url()
                                ->maxLength(255),
                            TextInput::make('mailcoach_url')
                                ->label('MailCoach URL')
                                ->url()
                                ->maxLength(255),
                            TextInput::make('bugsnag_project_url')
                                ->label('Bugsnag project URL')
                                ->url()
                                ->maxLength(255),
                            TextInput::make('bugsnag_project_key')
                                ->label('Bugsnag project API key')
                                ->helperText('The project notifier key. Used to match this site to its Bugsnag project.')
                                ->password()
                                ->revealable()
                                ->maxLength(255),
                            TextInput::make('bugsnag_project_id')
                                ->label('Bugsnag project ID')
                                ->helperText('Filled in by "php artisan bugsnag:link-projects"; set it by hand to override.')
                                ->maxLength(255),
                        ]),
                    ]),
            ]);
    }
}
