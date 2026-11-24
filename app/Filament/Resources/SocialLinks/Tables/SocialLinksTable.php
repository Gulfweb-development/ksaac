<?php

namespace App\Filament\Resources\SocialLinks\Tables;

use App\Enums\Site;
use App\Enums\SocialPlatform;
use App\Filament\Exports\SocialLinkExporter;
use App\Filament\Support\TableExports;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SocialLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('platform')->badge()->color('gray'),
                TextColumn::make('url')->label('URL')->searchable()->limit(50)->url(fn ($record) => $record->url, shouldOpenInNewTab: true),
                TextColumn::make('site')->badge(),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                SelectFilter::make('site')->options(Site::class),
                SelectFilter::make('platform')->options(SocialPlatform::class),
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(SocialLinkExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(SocialLinkExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
