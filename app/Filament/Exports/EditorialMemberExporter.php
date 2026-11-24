<?php

namespace App\Filament\Exports;

use App\Models\EditorialMember;
use Filament\Actions\Exports\ExportColumn;

class EditorialMemberExporter extends BaseExporter
{
    protected static ?string $model = EditorialMember::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('group')->label(__('Board'))->formatStateUsing(fn ($state) => $state?->getLabel()),
            ...self::translated('name'),
            ...self::translated('role', 'Role on the board'),
            self::yesNo('is_deceased', 'Deceased'),
            ExportColumn::make('deceased_on')->label(__('Date of death')),
            self::yesNo('is_active', 'Visible'),
        ];
    }
}
