<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

// Case order = sidebar order.
enum NavigationGroup implements HasLabel
{
    case Ksaac;
    case Jac;
    case Shared;
    case Settings;

    public function getLabel(): string
    {
        return match ($this) {
            self::Ksaac => __('KSAAC'),
            self::Jac => __('Arab Childhood Journal'),
            self::Shared => __('Shared'),
            self::Settings => __('Settings'),
        };
    }
}
