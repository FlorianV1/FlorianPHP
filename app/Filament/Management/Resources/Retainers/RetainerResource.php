<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Retainers;

use App\Filament\Management\Resources\Retainers\Pages\CreateRetainer;
use App\Filament\Management\Resources\Retainers\Pages\EditRetainer;
use App\Filament\Management\Resources\Retainers\Pages\ListRetainers;
use App\Filament\Management\Resources\Retainers\Schemas\RetainerForm;
use App\Filament\Management\Resources\Retainers\Tables\RetainersTable;
use App\Models\Retainer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class RetainerResource extends Resource
{
    protected static ?string $model = Retainer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return RetainerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RetainersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRetainers::route('/'),
            'create' => CreateRetainer::route('/create'),
            'edit' => EditRetainer::route('/{record}/edit'),
        ];
    }
}
