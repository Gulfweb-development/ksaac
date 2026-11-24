<?php

namespace App\Filament\Exports;

use App\Models\Project;
use Filament\Actions\Exports\ExportColumn;

class ProjectExporter extends BaseExporter
{
    protected static ?string $model = Project::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ...self::translated('summary'),
            ExportColumn::make('started_on')->label(__('Start date')),
            self::yesNo('is_published', 'Published'),
            self::yesNo('is_featured', 'Featured'),
        ];
    }
}
