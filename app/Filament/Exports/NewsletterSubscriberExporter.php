<?php

namespace App\Filament\Exports;

use App\Models\NewsletterSubscriber;
use Filament\Actions\Exports\ExportColumn;

class NewsletterSubscriberExporter extends BaseExporter
{
    protected static ?string $model = NewsletterSubscriber::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('email'),
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('locale'),
            ExportColumn::make('status')->state(fn (NewsletterSubscriber $record) => $record->unsubscribed_at ? __('Unsubscribed') : __('Subscribed')),
            ExportColumn::make('created_at')->label(__('Subscribed on')),
            ExportColumn::make('unsubscribed_at'),
        ];
    }
}
