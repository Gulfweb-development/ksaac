<?php

namespace App\Filament\Exports;

use App\Models\Issue;
use Filament\Actions\Exports\ExportColumn;

class IssueExporter extends BaseExporter
{
    protected static ?string $model = Issue::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('number')->label(__('Issue')),
            ...self::translated('title'),
            ExportColumn::make('volume.number')->label(__('Volume')),
            ExportColumn::make('published_on')->label(__('Publish date')),
            ExportColumn::make('articles_count')->counts('articles')->label(__('Articles')),
            self::yesNo('is_current', 'Current issue'),
            self::yesNo('is_published', 'Published'),
        ];
    }
}
