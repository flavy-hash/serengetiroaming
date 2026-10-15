<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuoteSource: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Website = 'website';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Website => 'Website',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'gray',
            self::Website => 'warning',
        };
    }
}
