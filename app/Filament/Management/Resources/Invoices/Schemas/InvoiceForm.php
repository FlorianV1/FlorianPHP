<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Website;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('client_id')
                                ->relationship('client', 'company_name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                            Select::make('website_id')
                                ->label('Website')
                                ->options(fn (Get $get): array => Website::query()
                                    ->where('client_id', $get('client_id'))
                                    ->pluck('label', 'id')
                                    ->all())
                                ->placeholder('— none —'),
                            TextInput::make('number')
                                ->default(fn (): string => Invoice::nextNumber())
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            DatePicker::make('issue_date')
                                ->default(now())
                                ->required(),
                            DatePicker::make('due_date')
                                ->default(now()->addDays(14))
                                ->required(),
                            Select::make('status')
                                ->options(InvoiceStatus::class)
                                ->default(InvoiceStatus::Draft)
                                ->required(),
                        ]),
                    ]),
                Section::make('Lines')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('lines')
                            ->relationship()
                            ->hiddenLabel()
                            ->columns(6)
                            ->defaultItems(1)
                            ->schema([
                                TextInput::make('description')
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateAmount($get, $set)),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateAmount($get, $set)),
                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('€')
                                    ->readOnly(),
                            ]),
                        Grid::make(3)->schema([
                            TextInput::make('vat_rate')
                                ->label('VAT %')
                                ->numeric()
                                ->default(21)
                                ->required()
                                ->live(onBlur: true),
                            TextInput::make('totals_preview')
                                ->label('Totals (computed)')
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(2)
                                ->placeholder(function (Get $get): string {
                                    $subtotal = collect($get('lines') ?? [])
                                        ->sum(fn (array $line): float => (float) ($line['amount'] ?? 0));
                                    $vat = round($subtotal * ((float) ($get('vat_rate') ?? 0) / 100), 2);

                                    return sprintf('Subtotal €%0.2f + VAT €%0.2f = €%0.2f', $subtotal, $vat, $subtotal + $vat);
                                }),
                        ]),
                    ]),
                Section::make('Extra')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('external_reference')
                                ->label('External reference (accounting id)')
                                ->maxLength(255),
                            Textarea::make('notes')
                                ->rows(2),
                        ]),
                    ]),
            ]);
    }

    private static function updateAmount(Get $get, Set $set): void
    {
        $set('amount', round((float) $get('quantity') * (float) $get('unit_price'), 2));
    }
}
