<?php

namespace App\Filament\Website\Resources\NowItems\Pages;

use App\Filament\Website\Resources\NowItems\NowItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNowItem extends EditRecord
{
    protected static string $resource = NowItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
