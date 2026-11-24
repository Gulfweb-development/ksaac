<?php

namespace App\Filament\Resources\Conferences\Tables;

use App\Filament\Exports\ConferenceExporter;
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

class ConferencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                Columns::image(),
                Columns::translatable('title')->description(fn ($record) => $record->venue),
                TextColumn::make('starts_on')->label('Start date')->date()->sortable(),
                Columns::toggle('is_published', 'Published'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                Filters::dateRange('starts_on', __('Start date')),
            ])
            ->headerActions([
                TableExports::header(ConferenceExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(ConferenceExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
