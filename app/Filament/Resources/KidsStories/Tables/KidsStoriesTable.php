<?php

namespace App\Filament\Resources\KidsStories\Tables;

use App\Filament\Exports\KidsStoryExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class KidsStoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                Columns::image('cover', __('Cover')),
                Columns::translatable('title'),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->headerActions([
                TableExports::header(KidsStoryExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(KidsStoryExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
