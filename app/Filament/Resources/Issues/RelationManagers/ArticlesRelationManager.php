<?php

namespace App\Filament\Resources\Issues\RelationManagers;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

/** The issue's table of contents; create/edit open the Article pages (table and form come from ArticleResource). */
class ArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'articles';

    protected static ?string $relatedResource = ArticleResource::class;

    public function table(Table $table): Table
    {
        $table->getColumn('issue.number')?->hidden();

        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            // Keep the Export button the Articles table already added.
            ->headerActions([
                ...$table->getHeaderActions(),
                CreateAction::make()->url(fn () => ArticleResource::getUrl('create', ['issue' => $this->getOwnerRecord()->getKey()])),
            ]);
    }
}
