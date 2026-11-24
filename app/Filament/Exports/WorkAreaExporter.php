<?php

namespace App\Filament\Exports;

use App\Models\WorkArea;

class WorkAreaExporter extends BaseExporter
{
    protected static ?string $model = WorkArea::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ...self::translated('description'),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
