<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Schemas;

use App\Enums\ClientStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('company_name')
                                ->required()
                                ->maxLength(255),
                            Select::make('status')
                                ->options(ClientStatus::class)
                                ->default(ClientStatus::Prospect)
                                ->required(),
                            TextInput::make('vat_number')
                                ->label('VAT number')
                                ->maxLength(255),
                            TextInput::make('registration_number')
                                ->maxLength(255),
                        ]),
                    ]),
                Section::make('Primary contact')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('contact_name')
                                ->maxLength(255),
                            TextInput::make('contact_email')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('contact_phone')
                                ->tel()
                                ->maxLength(255),
                        ]),
                    ]),
                Section::make('Billing')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('hourly_rate')
                                ->numeric()
                                ->prefix('€')
                                ->minValue(0),
                            TextInput::make('currency')
                                ->required()
                                ->default('EUR')
                                ->maxLength(3),
                            DatePicker::make('onboarded_at'),
                            Textarea::make('billing_address')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),
                Section::make('Notes')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        RichEditor::make('notes')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
