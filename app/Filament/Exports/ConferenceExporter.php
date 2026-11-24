<?php

namespace App\Filament\Exports;

use App\Models\Conference;
use Filament\Actions\Exports\ExportColumn;

class ConferenceExporter extends BaseExporter
{
    protected static ?string $model = Conference::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ...self::translated('venue'),
            ExportColumn::make('starts_on')->label(__('Start date')),
            ExportColumn::make('ends_on')->label(__('End date')),
            self::yesNo('is_published', 'Published'),
        ];
    }
}
