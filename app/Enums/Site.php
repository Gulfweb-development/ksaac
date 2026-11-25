<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Which public website a record belongs to. */
enum Site: string implements HasColor, HasLabel
{
    case Ksaac = 'ksaac';
    case Jac = 'jac';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ksaac => __('KSAAC'),
            self::Jac => __('Journal of Arab Children'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ksaac => 'info',
            self::Jac => 'success',
        };
    }
}
