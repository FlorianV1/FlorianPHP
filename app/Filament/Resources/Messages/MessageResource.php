<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Filament\Resources\Messages\Pages\ViewMessage;
use App\Filament\Resources\Messages\Tables\MessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $navigationLabel = 'Messages';
    protected static ?string $modelLabel = 'Message';
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::unread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label('From'),

                Infolists\Components\TextEntry::make('email')
                    ->label('Email')
                    ->copyable()
                    ->url(fn ($record) => 'mailto:' . $record->email),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y H:i'),

                Infolists\Components\TextEntry::make('message')
                    ->label('Message')
                    ->columnSpanFull()
                    ->prose(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'view'  => ViewMessage::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
