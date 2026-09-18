<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients;

use App\Filament\Management\Resources\Clients\Pages\CreateClient;
use App\Filament\Management\Resources\Clients\Pages\EditClient;
use App\Filament\Management\Resources\Clients\Pages\ListClients;
use App\Filament\Management\Resources\Clients\Pages\ManageClientActivities;
use App\Filament\Management\Resources\Clients\Pages\ManageClientContacts;
use App\Filament\Management\Resources\Clients\Pages\ManageClientInvoices;
use App\Filament\Management\Resources\Clients\Pages\ManageClientRetainers;
use App\Filament\Management\Resources\Clients\Pages\ManageClientWebsites;
use App\Filament\Management\Resources\Clients\Pages\ViewClient;
use App\Filament\Management\Resources\Clients\Schemas\ClientForm;
use App\Filament\Management\Resources\Clients\Schemas\ClientInfolist;
use App\Filament\Management\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'company_name';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'company_name',
            'contact_name',
            'contact_email',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewClient::class,
            EditClient::class,
            ManageClientWebsites::class,
            ManageClientInvoices::class,
            ManageClientRetainers::class,
            ManageClientContacts::class,
            ManageClientActivities::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
            'websites' => ManageClientWebsites::route('/{record}/websites'),
            'invoices' => ManageClientInvoices::route('/{record}/invoices'),
            'retainers' => ManageClientRetainers::route('/{record}/retainers'),
            'contacts' => ManageClientContacts::route('/{record}/contacts'),
            'activities' => ManageClientActivities::route('/{record}/activity'),
        ];
    }
}
