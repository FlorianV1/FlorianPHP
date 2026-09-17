<?php

namespace App\Filament\Website\Resources\NowItems\Pages;

use App\Filament\Website\Resources\NowItems\NowItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNowItem extends CreateRecord
{
    protected static string $resource = NowItemResource::class;
}
