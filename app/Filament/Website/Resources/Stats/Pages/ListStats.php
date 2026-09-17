<?php

namespace App\Filament\Website\Resources\Stats\Pages;

use App\Filament\Website\Resources\Stats\StatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStats extends ListRecords
{
    protected static string $resource = StatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
