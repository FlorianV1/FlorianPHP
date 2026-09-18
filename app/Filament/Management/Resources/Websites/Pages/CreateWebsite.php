<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Websites\Pages;

use App\Filament\Management\Resources\Websites\WebsiteResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateWebsite extends CreateRecord
{
    protected static string $resource = WebsiteResource::class;
}
