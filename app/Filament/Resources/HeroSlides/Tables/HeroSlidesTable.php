<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use App\Enums\Site;
use App\Filament\Exports\HeroSlideExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Models\HeroSlide;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('site')->badge(),
                IconColumn::make('has_video')->label(__('Video'))->boolean()->state(fn (HeroSlide $record) => $record->hasMedia('video')),
                Columns::toggle(),
            ])
            ->filters([
                SelectFilter::make('site')->options(Site::class),
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
