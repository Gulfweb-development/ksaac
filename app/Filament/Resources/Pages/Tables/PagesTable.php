<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\Site;
use App\Filament\Exports\PageExporter;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')->label('Image')->collection('featured')->square()->toggleable(),
                TextColumn::make('title')
                    // Title is JSON {ar, en}; search both languages.
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('title', 'like', "%{$search}%"))
                    ->description(fn ($record) => '/'.$record->slug),
                TextColumn::make('site')->badge()->sortable(),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('site')->options(Site::class),
                TernaryFilter::make('is_published')->label('Published'),
                Filters::dateRange('updated_at'),
            ])
            ->headerActions([
                TableExports::header(PageExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(PageExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
