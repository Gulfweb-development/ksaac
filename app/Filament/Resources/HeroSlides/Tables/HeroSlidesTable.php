<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use App\Filament\Exports\HeroSlideExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                Columns::image(),
                Columns::translatable('title'),
                Columns::toggle(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(HeroSlideExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(HeroSlideExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
