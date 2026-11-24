<?php

namespace App\Filament\Exports;

use App\Models\Page;
use Filament\Actions\Exports\ExportColumn;

class PageExporter extends BaseExporter
{
    protected static ?string $model = Page::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ExportColumn::make('slug'),
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            self::yesNo('is_published', 'Published'),
            ExportColumn::make('updated_at'),
        ];
    }
}
