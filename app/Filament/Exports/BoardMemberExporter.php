<?php

namespace App\Filament\Exports;

use App\Models\BoardMember;

class BoardMemberExporter extends BaseExporter
{
    protected static ?string $model = BoardMember::class;

    public static function getColumns(): array
    {
        return [
            ...self::translated('name'),
            ...self::translated('position'),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
