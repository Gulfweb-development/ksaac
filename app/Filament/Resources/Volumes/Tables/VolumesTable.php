<?php

namespace App\Filament\Resources\Volumes\Tables;

use App\Filament\Exports\VolumeExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use App\Models\Volume;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VolumesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('number', 'desc')
            ->columns([
                TextColumn::make('number')->label('Volume')->sortable()->weight('bold'),
                Columns::translatable('title'),
                TextColumn::make('year')->sortable(),
                TextColumn::make('issues_count')->counts('issues')->label('Issues')->badge(),
            ])
            ->filters([
                SelectFilter::make('year')->options(fn () => Volume::query()->whereNotNull('year')->orderByDesc('year')->pluck('year', 'year')->all()),
            ])
            ->headerActions([
                TableExports::header(VolumeExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(VolumeExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
