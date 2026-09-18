<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\TimeEntries\Pages;

use App\Filament\Management\Resources\TimeEntries\TimeEntryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateTimeEntry extends CreateRecord
{
    protected static string $resource = TimeEntryResource::class;
}
