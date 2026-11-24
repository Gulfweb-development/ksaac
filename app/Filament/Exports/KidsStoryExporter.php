<?php

namespace App\Filament\Exports;

use App\Models\KidsStory;

class KidsStoryExporter extends BaseExporter
{
    protected static ?string $model = KidsStory::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('title'),
            self::yesNo('is_published', 'Published'),
        ];
    }
}
