<?php

namespace App\Filament\Exports;

use App\Models\Page;
use Filament\Actions\Exports\ExportColumn;

class PageExporter extends BaseExporter
{
    protected static ?string $model = Page::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('title_ar')->label(__('Title (Arabic)'))->state(fn (Page $record) => $record->getTranslation('title', 'ar', false)),
            ExportColumn::make('title_en')->label(__('Title (English)'))->state(fn (Page $record) => $record->getTranslation('title', 'en', false)),
            ExportColumn::make('slug'),
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('is_published')->label(__('Published'))->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No')),
            ExportColumn::make('updated_at'),
        ];
    }
}
