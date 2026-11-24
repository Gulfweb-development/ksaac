<?php

namespace App\Filament\Resources\Videos\Tables;

use App\Filament\Exports\VideoExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use App\Models\Video;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('thumbnail')->label('Preview')->state(fn (Video $record) => $record->thumbnailUrl())->height(48),
                Columns::translatable('title'),
                TextColumn::make('youtube_url')->label('YouTube link')->limit(40)->url(fn (Video $record) => $record->youtube_url, shouldOpenInNewTab: true)->toggleable(),
                Columns::toggle(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(VideoExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(VideoExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
