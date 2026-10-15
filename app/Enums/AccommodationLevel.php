<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccommodationLevel: string implements HasColor, HasLabel
{
    case Budget = 'budget';
    case MidRange = 'mid-range';
    case Luxury = 'luxury';

    public function getLabel(): string
    {
        return match ($this) {
            self::Budget => 'Budget',
            self::MidRange => 'Mid-range',
            self::Luxury => 'Luxury',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Budget => 'gray',
            self::MidRange => 'primary',
            self::Luxury => 'warning',
        };
    }
}
