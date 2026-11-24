<?php

namespace App\Filament\Exports;

use App\Models\SocialLink;
use Filament\Actions\Exports\ExportColumn;

class SocialLinkExporter extends BaseExporter
{
    protected static ?string $model = SocialLink::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('platform')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('url')->label('URL'),
            ExportColumn::make('is_active')->label(__('Visible'))->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No')),
        ];
    }
}
