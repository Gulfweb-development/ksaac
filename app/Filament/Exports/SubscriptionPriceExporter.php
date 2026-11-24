<?php

namespace App\Filament\Exports;

use App\Models\SubscriptionPrice;
use Filament\Actions\Exports\ExportColumn;

class SubscriptionPriceExporter extends BaseExporter
{
    protected static ?string $model = SubscriptionPrice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('plan')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('region')->label(__('Region'))->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('amount')->label(__('Price')),
            ExportColumn::make('currency'),
        ];
    }
}
