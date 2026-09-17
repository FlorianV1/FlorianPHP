<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WebsiteEnvironment: string implements HasColor, HasLabel
{
    case Production = 'production';
    case Staging = 'staging';

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
            self::Production => 'Production',
            self::Staging => 'Staging',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Production => 'success',
            self::Staging => 'warning',
        };
    }
}
