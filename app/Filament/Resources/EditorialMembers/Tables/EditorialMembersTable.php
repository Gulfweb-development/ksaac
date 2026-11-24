<?php

namespace App\Filament\Resources\EditorialMembers\Tables;

use App\Enums\EditorialGroup;
use App\Filament\Exports\EditorialMemberExporter;
use App\Filament\Support\Columns;
use App\Filament\Support\TableExports;
use App\Models\EditorialMember;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EditorialMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->defaultGroup(
                Group::make('group')
                    ->label(__('Board'))
                    ->getTitleFromRecordUsing(fn (EditorialMember $record) => $record->group->getLabel())
                    // Same order as the journal's board page: trustees, advisory, editorial.
                    ->orderQueryUsing(fn (Builder $query) => $query->orderByRaw('CASE '.$query->getQuery()->getGrammar()->wrap('group')." WHEN 'trustees' THEN 1 WHEN 'advisory' THEN 2 ELSE 3 END"))
            )
            ->columns([
                Columns::image('photo', __('Photo'))->circular(),
                Columns::translatable('name')->description(fn (EditorialMember $record) => $record->role),
                IconColumn::make('is_deceased')->label('Deceased')->boolean()->trueIcon('heroicon-o-minus-circle')->falseIcon('')->trueColor('gray'),
                Columns::toggle(),
            ])
            ->filters([
                SelectFilter::make('group')->label('Board')->options(EditorialGroup::class),
                TernaryFilter::make('is_deceased')->label('Deceased'),
                TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->headerActions([
                TableExports::header(EditorialMemberExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(EditorialMemberExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
