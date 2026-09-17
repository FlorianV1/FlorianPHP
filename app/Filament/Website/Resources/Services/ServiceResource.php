<?php

namespace App\Filament\Website\Resources\Services;

use App\Filament\Website\Resources\Services\Pages\CreateService;
use App\Filament\Website\Resources\Services\Pages\EditService;
use App\Filament\Website\Resources\Services\Pages\ListServices;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Services';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Squares2x2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->placeholder('Custom web applications')
                ->columnSpanFull(),

            Textarea::make('description')
                ->required()
                ->rows(3)
                ->maxLength(1000)
                ->placeholder('One or two sentences on what this is and who it is for.')
                ->columnSpanFull(),

            TextInput::make('icon')
                ->placeholder('heroicon-o-code-bracket')
                ->helperText('Heroicon name — browse them at heroicons.com')
                ->columnSpanFull(),

            TextInput::make('sort_order')
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
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('icon')
                    ->placeholder('—')
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->width('80px'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
