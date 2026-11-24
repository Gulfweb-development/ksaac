<?php

namespace App\Filament\Exports;

use App\Models\Publication;
use Filament\Actions\Exports\ExportColumn;

class PublicationExporter extends BaseExporter
{
    protected static ?string $model = Publication::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ...self::translated('author', 'Author or editor'),
            ExportColumn::make('category.name')->label(__('Category')),
            ExportColumn::make('year'),
            ExportColumn::make('purchase_url')->label(__('Purchase link')),
            self::yesNo('is_published', 'Published'),
        ];
    }
}
