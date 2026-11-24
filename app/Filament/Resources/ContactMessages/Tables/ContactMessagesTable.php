<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\Site;
use App\Filament\Exports\ContactMessageExporter;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use App\Models\ContactMessage;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->state(fn (ContactMessage $record) => $record->read_at === null)
                    ->boolean()
                    ->trueIcon(Heroicon::Envelope)
                    ->falseIcon(Heroicon::OutlinedEnvelopeOpen)
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->tooltip(fn (ContactMessage $record) => $record->read_at ? __('Read') : __('Unread')),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subject')->searchable()->limit(50)->placeholder('—'),
                TextColumn::make('message')->searchable()->limit(60)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('site')->badge()->sortable(),
                TextColumn::make('created_at')->label('Received at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('site')->options(Site::class),
                TernaryFilter::make('read')
                    ->label('Status')
                    ->trueLabel(__('Read'))
                    ->falseLabel(__('Unread'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('read_at'),
                        false: fn (Builder $q) => $q->whereNull('read_at'),
                    ),
                Filters::dateRange('created_at', __('Received at')),
            ])
            ->headerActions([
                TableExports::header(ContactMessageExporter::class),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(ContactMessageExporter::class),
                    BulkAction::make('markRead')
                        ->label(__('Mark as read'))
                        ->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => ContactMessage::whereKey($records->modelKeys())->whereNull('read_at')->update(['read_at' => now()])),
                    BulkAction::make('markUnread')
                        ->label(__('Mark as unread'))
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => ContactMessage::whereKey($records->modelKeys())->update(['read_at' => null])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
