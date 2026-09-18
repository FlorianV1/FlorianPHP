<?php

namespace App\Filament\Portfolio\Resources\NowItems\Pages;

use App\Filament\Portfolio\Resources\NowItems\NowItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNowItem extends CreateRecord
{
    protected static string $resource = NowItemResource::class;
}
