<?php

namespace App\Filament\Portfolio\Resources\Settings\Pages;

use App\Filament\Portfolio\Resources\Settings\SettingsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSettings extends EditRecord
{
    protected static string $resource = SettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
