<?php

namespace App\Filament\Exports;

use App\Models\Stat;
use Filament\Actions\Exports\ExportColumn;

class StatExporter extends BaseExporter
{
    protected static ?string $model = Stat::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('value'),
            ExportColumn::make('suffix'),
            ...self::translated('label'),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
