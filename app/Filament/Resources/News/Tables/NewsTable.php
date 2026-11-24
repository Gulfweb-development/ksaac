<?php

namespace App\Filament\Resources\News\Tables;

use App\Filament\Exports\NewsExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                Columns::image(),
                Columns::translatable('title')->description(fn ($record) => '/'.$record->slug),
                TextColumn::make('published_at')->label('Publish date')->date()->sortable(),
                Columns::toggle('is_featured', 'Featured'),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
                Filters::dateRange('published_at', __('Publish date')),
            ])
            ->headerActions([
                TableExports::header(NewsExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(NewsExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
