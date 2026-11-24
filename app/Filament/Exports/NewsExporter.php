<?php

namespace App\Filament\Exports;

use App\Models\News;
use Filament\Actions\Exports\ExportColumn;

class NewsExporter extends BaseExporter
{
    protected static ?string $model = News::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ExportColumn::make('slug'),
            ExportColumn::make('published_at')->label(__('Publish date')),
            self::yesNo('is_published', 'Published'),
            self::yesNo('is_featured', 'Featured'),
        ];
    }
}
