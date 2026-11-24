<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;

/** Table columns reused across content resources. */
class Columns
{
    /** Shows the current-locale value; search matches either language in the JSON column. */
    public static function translatable(string $name = 'title'): TextColumn
    {
        return TextColumn::make($name)
            ->searchable(query: fn (Builder $query, string $search) => $query->where($name, 'like', "%{$search}%"))
            ->wrap();
    }

    public static function image(string $collection = 'image', ?string $label = null): SpatieMediaLibraryImageColumn
    {
        return SpatieMediaLibraryImageColumn::make($collection)
            ->label($label ?? __('Image'))
            ->collection($collection)
            ->square()
            ->toggleable();
    }

    public static function toggle(string $name = 'is_active', string $label = 'Visible'): ToggleColumn
    {
        return ToggleColumn::make($name)->label(__($label));
    }
}
