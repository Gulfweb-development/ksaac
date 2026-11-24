<?php

namespace App\Filament\Exports;

use App\Models\PublicationCategory;
use Filament\Actions\Exports\ExportColumn;

class PublicationCategoryExporter extends BaseExporter
{
    protected static ?string $model = PublicationCategory::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('name'),
            ExportColumn::make('slug'),
            ExportColumn::make('publications_count')->counts('publications')->label(__('Publications')),
        ];
    }
}
