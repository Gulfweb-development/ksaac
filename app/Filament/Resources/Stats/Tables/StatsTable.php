<?php

namespace App\Filament\Resources\Stats\Tables;

use App\Filament\Exports\StatExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('value')->formatStateUsing(fn ($state, $record) => $state.$record->suffix)->weight('bold'),
                Columns::translatable('label'),
                IconColumn::make('animate')->label('Count up')->boolean(),
                Columns::toggle(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(StatExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(StatExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
