<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Clients\Pages;

use App\Filament\Management\Resources\Clients\ClientResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;
}
