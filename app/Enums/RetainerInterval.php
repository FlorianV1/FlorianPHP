<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RetainerInterval: string implements HasColor, HasLabel
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function months(): int
    {
        return match ($this) {
            self::Monthly => 1,
            self::Quarterly => 3,
            self::Yearly => 12,
        };
    }

    /**
     * Normalize an amount billed at this interval to its monthly equivalent.
     */
    public function monthlyAmount(float $amount): float
    {
        return round($amount / $this->months(), 2);
    }

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
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Yearly => 'Yearly',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Monthly => 'success',
            self::Quarterly => 'info',
            self::Yearly => 'gray',
        };
    }
}
