<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** The three bodies listed on the journal's editorial board page, in display order. */
enum EditorialGroup: string implements HasLabel
{
    case Trustees = 'trustees';
    case Advisory = 'advisory';
    case Editorial = 'editorial';

    public function getLabel(): string
    {
        return match ($this) {
            self::Trustees => __('Board of trustees'),
            self::Advisory => __('Advisory board'),
            self::Editorial => __('Editorial board'),
        };
    }
}
