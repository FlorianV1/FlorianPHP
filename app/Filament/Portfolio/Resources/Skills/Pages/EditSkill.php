<?php

namespace App\Filament\Portfolio\Resources\Skills\Pages;

use App\Filament\Portfolio\Resources\Skills\SkillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSkill extends EditRecord
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
