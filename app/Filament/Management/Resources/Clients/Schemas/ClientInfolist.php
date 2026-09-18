<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Schemas;

use App\Models\Client;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Overview')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('status')
                                ->badge(),
                            TextEntry::make('outstanding')
                                ->label('Outstanding')
                                ->state(fn (Client $record): float => $record->outstandingBalance())
                                ->money('EUR'),
                            TextEntry::make('mrr')
                                ->label('Monthly recurring')
                                ->state(fn (Client $record): float => $record->monthlyRecurringRevenue())
                                ->money('EUR'),
                            TextEntry::make('hourly_rate')
                                ->money('EUR'),
                        ]),
                    ]),
                Section::make('Contact & billing')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('contact_name'),
                        TextEntry::make('contact_email')
                            ->copyable(),
                        TextEntry::make('contact_phone'),
                        TextEntry::make('vat_number')
                            ->label('VAT number'),
                        TextEntry::make('billing_address'),
                        TextEntry::make('onboarded_at')
                            ->date(),
                    ]),
                Section::make('Notes')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('notes')
                            ->html()
                            ->hiddenLabel(),
                    ]),
            ]);
    }
}
