<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Pages;

use App\Filament\Management\Resources\Clients\ClientResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Overview';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
