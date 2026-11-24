<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReviewStatus: string implements HasColor, HasLabel
{
    case Invited = 'invited';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Invited => __('Invited'),
            self::Accepted => __('In progress'),
            self::Declined => __('Declined'),
            self::Completed => __('Completed'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Invited => 'gray',
            self::Accepted => 'info',
            self::Declined => 'danger',
            self::Completed => 'success',
        };
    }
}
