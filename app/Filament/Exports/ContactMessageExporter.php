<?php

namespace App\Filament\Exports;

use App\Models\ContactMessage;
use Filament\Actions\Exports\ExportColumn;

class ContactMessageExporter extends BaseExporter
{
    protected static ?string $model = ContactMessage::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')->label(__('Received at')),
            ExportColumn::make('site')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('subject'),
            ExportColumn::make('message'),
            ExportColumn::make('status')->state(fn (ContactMessage $record) => $record->read_at ? __('Read') : __('Unread')),
        ];
    }
}
