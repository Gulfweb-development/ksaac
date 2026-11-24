<?php

namespace App\Filament\Resources\Publications\Tables;

use App\Filament\Exports\PublicationExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use App\Models\Publication;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                Columns::image('cover', __('Cover')),
                Columns::translatable('title')->description(fn ($record) => $record->author),
                TextColumn::make('category.name')->label('Category')->badge()->color('gray'),
                TextColumn::make('year')->sortable(),
                Columns::toggle('is_featured', 'Featured'),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                SelectFilter::make('publication_category_id')->label('Category')->relationship('category', 'slug')->getOptionLabelFromRecordUsing(fn ($record) => $record->name)->preload(),
                SelectFilter::make('year')->options(fn () => Publication::query()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year', 'year')->all()),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->headerActions([
                TableExports::header(PublicationExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(PublicationExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
