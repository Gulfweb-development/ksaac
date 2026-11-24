<?php

namespace App\Filament\Exports;

use App\Models\Subscription;
use Filament\Actions\Exports\ExportColumn;

class SubscriptionExporter extends BaseExporter
{
    protected static ?string $model = Subscription::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('plan')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('region')->label(__('Region'))->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('issue.number')->label(__('Issue')),
            ExportColumn::make('amount')->label(__('Price')),
            ExportColumn::make('currency'),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('starts_on')->label(__('Start date')),
            ExportColumn::make('ends_on')->label(__('Valid until')),
            self::yesNo('is_renewal', 'Renewal'),
            ExportColumn::make('created_at')->label(__('Requested on')),
        ];
    }
}
