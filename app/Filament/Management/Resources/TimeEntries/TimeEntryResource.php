<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\TimeEntries;

use App\Filament\Management\Resources\TimeEntries\Pages\CreateTimeEntry;
use App\Filament\Management\Resources\TimeEntries\Pages\EditTimeEntry;
use App\Filament\Management\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Filament\Management\Resources\TimeEntries\Schemas\TimeEntryForm;
use App\Filament\Management\Resources\TimeEntries\Tables\TimeEntriesTable;
use App\Models\TimeEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return TimeEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntries::route('/'),
            'create' => CreateTimeEntry::route('/create'),
            'edit' => EditTimeEntry::route('/{record}/edit'),
        ];
    }
}
