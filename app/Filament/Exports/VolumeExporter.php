<?php

namespace App\Filament\Exports;

use App\Models\Volume;
use Filament\Actions\Exports\ExportColumn;

class VolumeExporter extends BaseExporter
{
    protected static ?string $model = Volume::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('number')->label(__('Volume')),
            ...self::translated('title'),
            ExportColumn::make('year'),
            ExportColumn::make('issues_count')->counts('issues')->label(__('Issues')),
        ];
    }
}
