<?php

namespace App\Filament\Resources\SubscriptionPrices;

use App\Enums\NavigationGroup;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Filament\Exports\SubscriptionPriceExporter;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\SubscriptionPrices\Pages\ListSubscriptionPrices;
use App\Filament\Support\TableExports;
use App\Models\SubscriptionPrice;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** The journal's fixed 3 × 3 price grid (plan × region). Amounts are edited directly in the table. */
class SubscriptionPriceResource extends BaseResource
{
    protected static ?string $model = SubscriptionPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Jac;

    protected static ?int $navigationSort = 8;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup(Group::make('plan')->label(__('Plan'))->getTitleFromRecordUsing(fn (SubscriptionPrice $record) => $record->plan->getLabel()))
            // Same order as the journal's price table: Kuwait, GCC, other countries.
            ->defaultSort(fn (Builder $query) => $query->orderByRaw("CASE region WHEN 'kuwait' THEN 1 WHEN 'gcc' THEN 2 ELSE 3 END"))
            ->paginated(false)
            ->columns([
                TextColumn::make('region')->label('Region'),
                TextInputColumn::make('amount')
                    ->label('Price')
                    ->type('number')
                    ->step('0.001')
                    ->rules(['required', 'numeric', 'min:0', 'max:99999']),
                TextColumn::make('currency')->badge()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('plan')->options(SubscriptionPlan::class),
                SelectFilter::make('region')->label('Region')->options(SubscriptionRegion::class),
            ])
            ->headerActions([
                TableExports::header(SubscriptionPriceExporter::class),
            ])
            ->toolbarActions([
                ...TableExports::bulk(SubscriptionPriceExporter::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPrices::route('/'),
        ];
    }
}
