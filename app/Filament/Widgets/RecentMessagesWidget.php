<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentMessagesWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Recent Messages';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ContactMessage::query()->latest()->limit(6)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->color('gray')
                    ->copyable()
                    ->limit(24),

                Tables\Columns\TextColumn::make('message')
                    ->limit(40)
                    ->color('gray'),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->color('gray'),
            ])
            ->paginated(false)
            ->striped();
    }
}
