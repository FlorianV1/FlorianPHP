<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Retainers\Pages;

use App\Filament\Management\Resources\Retainers\RetainerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditRetainer extends EditRecord
{
    protected static string $resource = RetainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
