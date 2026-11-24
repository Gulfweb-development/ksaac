<?php

namespace App\Filament\Exports;

use App\Models\Admin;
use Filament\Actions\Exports\ExportColumn;

class AdminExporter extends BaseExporter
{
    protected static ?string $model = Admin::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('mobile'),
            ExportColumn::make('roles.name')->label('Roles'),
            ExportColumn::make('created_at'),
        ];
    }
}
