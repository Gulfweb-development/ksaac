<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

abstract class BaseExporter extends Exporter
{
    /** One column per language for a translatable attribute, e.g. "Title (Arabic)" and "Title (English)". */
    protected static function translated(string $attribute, ?string $label = null): array
    {
        $label ??= str($attribute)->replace('_', ' ')->ucfirst()->toString();

        return collect(config('app.locale_labels'))
            ->map(fn (string $language, string $locale) => ExportColumn::make("{$attribute}_{$locale}")
                ->label(__($label).' ('.$language.')')
                ->state(fn ($record) => $record->getTranslation($attribute, $locale, false)))
            ->values()
            ->all();
    }

    protected static function yesNo(string $attribute, string $label): ExportColumn
    {
        return ExportColumn::make($attribute)->label(__($label))->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No'));
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('Export completed: :count rows exported.', ['count' => number_format($export->successful_rows)]);

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.__(':count rows failed.', ['count' => number_format($failed)]);
        }

        return $body;
    }
}
