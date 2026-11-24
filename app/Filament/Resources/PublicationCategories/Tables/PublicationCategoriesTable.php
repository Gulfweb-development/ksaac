<?php

namespace App\Filament\Resources\PublicationCategories\Tables;

use App\Filament\Exports\PublicationCategoryExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicationCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                Columns::translatable('name'),
                TextColumn::make('slug'),
                TextColumn::make('publications_count')->counts('publications')->label('Publications')->badge(),
            ])
            ->filters([

            ])
            ->headerActions([
                TableExports::header(PublicationCategoryExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(PublicationCategoryExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
