<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

/** Filters reused across admin tables. */
class Filters
{
    public static function dateRange(string $column = 'created_at', ?string $label = null): Filter
    {
        $label ??= __(str($column)->replace('_', ' ')->ucfirst()->toString());

        return Filter::make($column.'_range')
            ->label($label)
            ->schema([
                DatePicker::make('from')->label(__('From').' ('.$label.')'),
                DatePicker::make('until')->label(__('Until').' ('.$label.')'),
            ])
            ->query(fn (Builder $query, array $data) => $query
                ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate($column, '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate($column, '<=', $date)))
            ->indicateUsing(fn (array $data): array => array_values(array_filter([
                ($data['from'] ?? null) ? Indicator::make(__('From').': '.$data['from']) : null,
                ($data['until'] ?? null) ? Indicator::make(__('Until').': '.$data['until']) : null,
            ])));
    }
}
