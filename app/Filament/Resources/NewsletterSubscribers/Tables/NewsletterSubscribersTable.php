<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Enums\Site;
use App\Filament\Exports\NewsletterSubscriberExporter;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->sortable()->copyable(),
                TextColumn::make('site')->badge()->sortable(),
                TextColumn::make('locale')->formatStateUsing(fn (string $state) => config("app.locale_labels.$state", $state)),
                TextColumn::make('status')
                    ->state(fn (NewsletterSubscriber $record) => $record->unsubscribed_at ? __('Unsubscribed') : __('Subscribed'))
                    ->badge()
                    ->color(fn (NewsletterSubscriber $record) => $record->unsubscribed_at ? 'gray' : 'success'),
                TextColumn::make('created_at')->label('Subscribed on')->dateTime()->sortable(),
                TextColumn::make('unsubscribed_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('site')->options(Site::class),
                TernaryFilter::make('subscribed')
                    ->label('Status')
                    ->trueLabel(__('Subscribed'))
                    ->falseLabel(__('Unsubscribed'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereNull('unsubscribed_at'),
                        false: fn (Builder $q) => $q->whereNotNull('unsubscribed_at'),
                    ),
                SelectFilter::make('locale')->options(config('app.locale_labels')),
                Filters::dateRange('created_at', __('Subscribed on')),
            ])
            ->headerActions([
                TableExports::header(NewsletterSubscriberExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    Action::make('toggleSubscription')
                        ->label(fn (NewsletterSubscriber $record) => $record->unsubscribed_at ? __('Resubscribe') : __('Unsubscribe'))
                        ->icon(fn (NewsletterSubscriber $record) => $record->unsubscribed_at ? Heroicon::OutlinedArrowUturnLeft : Heroicon::OutlinedNoSymbol)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (NewsletterSubscriber $record) => $record->update(['unsubscribed_at' => $record->unsubscribed_at ? null : now()])),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(NewsletterSubscriberExporter::class),
                    BulkAction::make('unsubscribe')
                        ->label(__('Unsubscribe selected'))
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => NewsletterSubscriber::whereKey($records->modelKeys())->whereNull('unsubscribed_at')->update(['unsubscribed_at' => now()])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
