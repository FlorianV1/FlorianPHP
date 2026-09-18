<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Retainers\Pages;

use App\Filament\Management\Resources\Retainers\RetainerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRetainer extends CreateRecord
{
    protected static string $resource = RetainerResource::class;
}
