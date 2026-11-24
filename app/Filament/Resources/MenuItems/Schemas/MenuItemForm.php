<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Enums\Site;
use App\Models\MenuItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    Select::make('site')->options(Site::class)->default(Site::Ksaac)->required()->live(),
                    Select::make('parent_id')
                        ->label('Parent item')
                        ->helperText(__('Empty = top menu item. Pick a top item to add a dropdown column, or a column to add a link inside it.'))
                        ->options(fn (Get $get, ?MenuItem $record) => MenuItem::query()
                            ->where('site', $get('site') instanceof Site ? $get('site')->value : $get('site'))
                            ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                            // Only levels 1 and 2 can have children (menu is at most 3 levels deep).
                            ->where(fn (Builder $q) => $q->whereNull('parent_id')->orWhereHas('parent', fn (Builder $p) => $p->whereNull('parent_id')))
                            ->with('parent')
                            ->orderBy('sort')
                            ->get()
                            ->mapWithKeys(fn (MenuItem $item) => [$item->getKey() => ($item->parent ? $item->parent->label.' › ' : '').$item->label]))
                        ->searchable(),
                    TextInput::make('label')->required()->maxLength(100)->translatableGroup(),
                    TextInput::make('url')->label('Link')->maxLength(255)->helperText(__('Leave empty for a dropdown column heading. Use /about for pages on this site.'))->columnSpanFull(),
                    TextInput::make('badge')->maxLength(30)->helperText(__('Optional small tag shown next to the item, e.g. "Stories".'))->translatableGroup(),
                    Toggle::make('open_in_new_tab'),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
