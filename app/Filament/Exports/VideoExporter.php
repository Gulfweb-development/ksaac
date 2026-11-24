<?php

namespace App\Filament\Exports;

use App\Models\Video;
use Filament\Actions\Exports\ExportColumn;

class VideoExporter extends BaseExporter
{
    protected static ?string $model = Video::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            ExportColumn::make('youtube_url')->label(__('YouTube link')),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
