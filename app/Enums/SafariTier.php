<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Budget levels for safari packages. The values match the navbar links
 * (/safaris#budget, /safaris#mid-range, …).
 */
enum SafariTier: string implements HasColor, HasLabel
{
    case Budget = 'budget';
    case Classic = 'classic';
    case MidRange = 'mid-range';
    case Luxury = 'luxury';

    public function getLabel(): string
    {
        return match ($this) {
            self::Budget => 'Budget',
            self::Classic => 'Classic',
            self::MidRange => 'Mid-range',
            self::Luxury => 'Luxury',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Budget => 'gray',
            self::Classic => 'info',
            self::MidRange => 'primary',
            self::Luxury => 'warning',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Budget => 'Camping and simple lodges — the same parks and guides, at the lowest price.',
            self::Classic => 'Comfortable tented camps and lodges with a good balance of value and comfort.',
            self::MidRange => 'Well-located lodges and permanent tented camps with more space and better views.',
            self::Luxury => 'Exclusive camps and lodges, private vehicles and the best locations in each park.',
        };
    }
}
