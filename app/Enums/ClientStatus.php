<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ClientStatus: string implements HasColor, HasLabel
{
    case Prospect = 'prospect';
    case Active = 'active';
    case Paused = 'paused';
    case Locked = 'locked';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function label(): string
    {
        return match ($this) {
            self::Prospect => 'Prospect',
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Locked => 'Locked',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Prospect => 'info',
            self::Active => 'success',
            self::Paused => 'warning',
            self::Locked => 'danger',
            self::Archived => 'gray',
        };
    }
}
