<?php

namespace App\Filament\Exports;

use App\Models\HeroSlide;
use Filament\Actions\Exports\ExportColumn;

class HeroSlideExporter extends BaseExporter
{
    protected static ?string $model = HeroSlide::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ...self::translated('kicker', 'Small heading'),
            ...self::translated('title'),
            ...self::translated('subtitle'),
            ExportColumn::make('button_url')->label(__('Button link')),
            ExportColumn::make('has_video')->label(__('Video'))->state(fn (HeroSlide $record) => $record->hasMedia('video') ? __('Yes') : __('No')),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
