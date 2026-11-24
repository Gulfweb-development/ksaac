<?php

namespace App\Filament\Resources\Issues\Tables;

use App\Filament\Exports\IssueExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use App\Models\Issue;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('number', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('volume'))
            ->columns([
                Columns::image('cover', __('Cover')),
                TextColumn::make('number')->label('Issue')->sortable()->weight('bold'),
                Columns::translatable('title')->description(fn (Issue $record) => __('Volume').' '.$record->volume?->number),
                TextColumn::make('published_on')->label('Publish date')->date()->sortable(),
                TextColumn::make('articles_count')->counts('articles')->label('Articles')->badge(),
                IconColumn::make('is_current')->label('Current')->boolean()->trueIcon('heroicon-s-star')->falseIcon(''),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                SelectFilter::make('volume_id')->label('Volume')->relationship('volume', 'number')->getOptionLabelFromRecordUsing(fn ($record) => $record->number.' — '.$record->title)->searchable()->preload(),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_current')->label('Current issue'),
                Filters::dateRange('published_on', __('Publish date')),
            ])
            ->headerActions([
                TableExports::header(IssueExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(IssueExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
