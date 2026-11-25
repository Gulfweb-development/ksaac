<?php

namespace App\Filament\Exports;

use App\Models\Award;
use Filament\Actions\Exports\ExportColumn;

class AwardExporter extends BaseExporter
{
    protected static ?string $model = Award::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ...self::translated('description'),
            ExportColumn::make('year')->label(__('Year')),
            ExportColumn::make('url')->label(__('Link')),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
