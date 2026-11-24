<?php

namespace App\Filament\Exports;

use App\Models\Article;
use Filament\Actions\Exports\ExportColumn;

class ArticleExporter extends BaseExporter
{
    protected static ?string $model = Article::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('issue.number')->label(__('Issue')),
            ...self::translated('title'),
            ExportColumn::make('authors_ar')->label(__('Authors').' (عربي)')->state(fn ($record) => $record->authorNames('ar')),
            ExportColumn::make('authors_en')->label(__('Authors').' (English)')->state(fn ($record) => $record->authorNames('en')),
            ExportColumn::make('type')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('language'),
            ExportColumn::make('pages')->label(__('Pages'))->state(fn ($record) => $record->pages()),
            ExportColumn::make('doi')->label('DOI'),
            self::yesNo('is_published', 'Published'),
        ];
    }
}
