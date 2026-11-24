<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleType;
use App\Filament\Exports\ArticleExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use App\Models\Article;
use App\Models\Issue;
use App\Models\Volume;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('issue'))
            ->columns([
                Columns::translatable('title')->description(fn (Article $record) => $record->authorNames()),
                TextColumn::make('issue.number')->label('Issue')->sortable(),
                TextColumn::make('type')->badge()->color('gray'),
                TextColumn::make('language')->formatStateUsing(fn (string $state) => config("app.locale_labels.$state", $state))->toggleable(),
                TextColumn::make('pages')->state(fn (Article $record) => $record->pages())->label('Pages')->toggleable(),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                SelectFilter::make('issue_id')->label('Issue')->relationship('issue', 'number')->getOptionLabelFromRecordUsing(fn (Issue $record) => __('Issue').' '.$record->number.' — '.$record->volume?->title)->searchable()->preload(),
                SelectFilter::make('volume')->label('Volume')->options(fn () => Volume::orderByDesc('number')->get()->mapWithKeys(fn (Volume $v) => [$v->id => $v->number.' — '.$v->title]))->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $q, $id) => $q->whereHas('issue', fn (Builder $i) => $i->where('volume_id', $id)))),
                SelectFilter::make('type')->options(ArticleType::class),
                SelectFilter::make('language')->label('Article language')->options(config('app.locale_labels')),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->headerActions([
                TableExports::header(ArticleExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(ArticleExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
