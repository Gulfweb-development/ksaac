<?php

namespace App\Filament\Resources\Partners\Tables;

use App\Filament\Exports\PartnerExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                // Logos keep their proportions; grey backing so white logos stay visible.
                Columns::image('logo', __('Logo'))->square(false)->imageHeight(36)->extraImgAttributes(['style' => 'object-fit: contain; background: #E5E7EB; padding: 4px; border-radius: 4px; max-width: 140px']),
                Columns::translatable('name'),
                TextColumn::make('url')->label('Website')->placeholder('—')->limit(40),
                Columns::toggle(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(PartnerExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(PartnerExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
