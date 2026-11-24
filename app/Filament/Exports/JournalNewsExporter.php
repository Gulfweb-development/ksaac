<?php

namespace App\Filament\Exports;

use App\Models\JournalNews;
use Filament\Actions\Exports\ExportColumn;

class JournalNewsExporter extends BaseExporter
{
    protected static ?string $model = JournalNews::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ExportColumn::make('slug'),
            ExportColumn::make('published_at')->label(__('Publish date')),
            self::yesNo('is_published', 'Published'),
        ];
    }
}
