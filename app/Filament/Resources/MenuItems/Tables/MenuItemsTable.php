<?php

namespace App\Filament\Resources\MenuItems\Tables;

use App\Enums\Site;
use App\Filament\Exports\MenuItemExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use App\Models\MenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            // Siblings sit together under their parent, so each dropdown reads in its own order.
            ->defaultGroup(
                Group::make('parent_id')
                    ->label(__('Inside'))
                    ->getTitleFromRecordUsing(fn (MenuItem $record) => $record->parent ? collect([$record->parent->parent?->label, $record->parent->label])->filter()->join(' › ') : __('Top menu'))
                    ->collapsible()
            )
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent.parent'))
            ->columns([
                Columns::translatable('label'),
                TextColumn::make('url')->label('Link')->placeholder('—')->limit(40),
                TextColumn::make('site')->badge(),
                Columns::toggle(),
            ])
            ->filters([
                SelectFilter::make('site')->options(Site::class),
                TernaryFilter::make('top_level')->label('Level')->trueLabel(__('Top menu items'))->falseLabel(__('Dropdown items'))->queries(true: fn (Builder $query) => $query->whereNull('parent_id'), false: fn (Builder $query) => $query->whereNotNull('parent_id')),
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(MenuItemExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(MenuItemExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
