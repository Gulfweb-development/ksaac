<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Enums\SubscriptionStatus;
use App\Filament\Exports\SubscriptionExporter;
use App\Filament\Resources\Subscriptions\SubscriptionActions;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use App\Models\Subscription;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->description(fn (Subscription $r) => $r->email)->wrap(),
                TextColumn::make('phone')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('plan')->wrap()->description(fn (Subscription $r) => $r->region->getLabel().($r->issue ? ' · '.__('Issue').' '.$r->issue->number : '')),
                TextColumn::make('amount')->label('Price')->formatStateUsing(fn ($state, Subscription $r) => rtrim(rtrim($state, '0'), '.').' '.$r->currency),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('ends_on')->label('Valid until')->date()->sortable()->placeholder('—')
                    ->color(fn (Subscription $r) => $r->ends_on?->isPast() ? 'danger' : null),
                IconColumn::make('has_receipt')->label('Receipt')->state(fn (Subscription $r) => $r->hasMedia('receipt'))->boolean()->trueIcon('heroicon-o-paper-clip')->falseIcon('heroicon-o-minus'),
                TextColumn::make('created_at')->label('Requested on')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(SubscriptionStatus::class),
                TernaryFilter::make('active')
                    ->label('Access now')
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Not active'))
                    ->queries(
                        true: fn (Builder $query) => $query->active(),
                        false: fn (Builder $query) => $query->whereNotIn('id', Subscription::query()->active()->select('id')),
                    ),
                SelectFilter::make('plan')->options(SubscriptionPlan::class),
                SelectFilter::make('region')->label('Region')->options(SubscriptionRegion::class),
                TernaryFilter::make('is_renewal')->label('Renewal'),
                Filters::dateRange('created_at', __('Requested on')),
                Filters::dateRange('ends_on', __('Valid until')),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['issue', 'media']))
            ->headerActions([
                TableExports::header(SubscriptionExporter::class),
            ])
            ->recordActions([
                SubscriptionActions::approve(),
                ActionGroup::make([
                    SubscriptionActions::reject(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(SubscriptionExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
