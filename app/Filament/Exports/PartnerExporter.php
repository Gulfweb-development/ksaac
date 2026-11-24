<?php

namespace App\Filament\Exports;

use App\Models\Partner;
use Filament\Actions\Exports\ExportColumn;

class PartnerExporter extends BaseExporter
{
    protected static ?string $model = Partner::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('name'),
            ExportColumn::make('url')->label(__('Website')),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
