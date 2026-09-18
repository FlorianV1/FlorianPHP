<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Websites;

use App\Filament\Management\Resources\Websites\Pages\CreateWebsite;
use App\Filament\Management\Resources\Websites\Pages\EditWebsite;
use App\Filament\Management\Resources\Websites\Pages\ListWebsites;
use App\Filament\Management\Resources\Websites\Pages\ViewWebsite;
use App\Filament\Management\Resources\Websites\Schemas\WebsiteForm;
use App\Filament\Management\Resources\Websites\Schemas\WebsiteInfolist;
use App\Filament\Management\Resources\Websites\Tables\WebsitesTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class WebsiteResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'label';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'label',
            'url',
            'client.company_name',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return WebsiteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WebsiteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsitesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsites::route('/'),
            'create' => CreateWebsite::route('/create'),
            'view' => ViewWebsite::route('/{record}'),
            'edit' => EditWebsite::route('/{record}/edit'),
        ];
    }
}
