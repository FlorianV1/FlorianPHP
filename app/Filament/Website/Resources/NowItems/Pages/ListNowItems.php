<?php

namespace App\Filament\Website\Resources\NowItems\Pages;

use App\Filament\Website\Resources\NowItems\NowItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNowItems extends ListRecords
{
    protected static string $resource = NowItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
