<?php

namespace App\Filament\Resources\JournalNews\Tables;

use App\Filament\Exports\JournalNewsExporter;
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

class JournalNewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                Columns::image(),
                Columns::translatable('title')->description(fn ($record) => '/'.$record->slug),
                TextColumn::make('published_at')->label('Publish date')->date()->sortable(),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                Filters::dateRange('published_at', __('Publish date')),
            ])
            ->headerActions([
                TableExports::header(JournalNewsExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(JournalNewsExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
