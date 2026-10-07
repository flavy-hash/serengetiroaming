<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReviewStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Waiting for approval',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Published => 'success',
            self::Rejected => 'gray',
        };
    }
}
