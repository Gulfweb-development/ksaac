<?php

namespace App\Filament\Exports;

use App\Models\MenuItem;
use Filament\Actions\Exports\ExportColumn;

class MenuItemExporter extends BaseExporter
{
    protected static ?string $model = MenuItem::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('label'),
            ExportColumn::make('parent.label')->label(__('Parent item')),
            ExportColumn::make('url')->label(__('Link')),
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
