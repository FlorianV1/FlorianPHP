<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Retainers\Pages;

use App\Filament\Management\Resources\Retainers\RetainerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListRetainers extends ListRecords
{
    protected static string $resource = RetainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
