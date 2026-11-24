<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionRegion: string implements HasLabel
{
    case Kuwait = 'kuwait';
    case Gcc = 'gcc';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kuwait => __('Inside Kuwait'),
            self::Gcc => __('GCC countries'),
            self::Other => __('Other countries'),
        };
    }

    public function currency(): string
    {
        return $this === self::Other ? 'USD' : 'KWD';
    }
}
