<?php

namespace App\Filament\Resources\WorkAreas\Tables;

use App\Filament\Exports\WorkAreaExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WorkAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                Columns::translatable('title'),
                Columns::translatable('description')->limit(80)->toggleable(),
                Columns::toggle(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(WorkAreaExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(WorkAreaExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
