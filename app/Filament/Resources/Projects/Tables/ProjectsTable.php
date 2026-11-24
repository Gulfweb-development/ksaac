<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Filament\Exports\ProjectExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('started_on', 'desc')
            ->columns([
                Columns::image(),
                Columns::translatable('title'),
                TextColumn::make('started_on')->label('Start date')->date()->sortable(),
                Columns::toggle('is_featured', 'Featured'),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->headerActions([
                TableExports::header(ProjectExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(ProjectExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
