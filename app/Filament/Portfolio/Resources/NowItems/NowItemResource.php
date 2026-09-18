<?php

namespace App\Filament\Portfolio\Resources\NowItems;

use App\Filament\Portfolio\Resources\NowItems\Pages\CreateNowItem;
use App\Filament\Portfolio\Resources\NowItems\Pages\EditNowItem;
use App\Filament\Portfolio\Resources\NowItems\Pages\ListNowItems;
use App\Models\NowItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class NowItemResource extends Resource
{
    protected static ?string $model = NowItem::class;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Now / Focus';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('description')
                ->required()
                ->maxLength(500)
                ->placeholder('Building a personal portfolio with Laravel & Filament')
                ->columnSpanFull(),

            TextInput::make('order')
                ->numeric()
                ->default(0)
                ->helperText('Lower = shown first'),

            Toggle::make('is_active')
                ->label('Show on portfolio')
                ->default(true)
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->searchable()
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('order')
                    ->sortable()
                    ->width('80px'),

                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNowItems::route('/'),
            'create' => CreateNowItem::route('/create'),
            'edit' => EditNowItem::route('/{record}/edit'),
        ];
    }
}
