<?php

namespace App\Filament\Website\Resources\Messages\Pages;

use App\Filament\Website\Resources\Messages\MessageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->record->markAsRead();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
