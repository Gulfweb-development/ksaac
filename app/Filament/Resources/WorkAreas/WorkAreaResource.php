<?php

namespace App\Filament\Resources\WorkAreas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\WorkAreas\Pages\CreateWorkArea;
use App\Filament\Resources\WorkAreas\Pages\EditWorkArea;
use App\Filament\Resources\WorkAreas\Pages\ListWorkAreas;
use App\Filament\Resources\WorkAreas\Schemas\WorkAreaForm;
use App\Filament\Resources\WorkAreas\Tables\WorkAreasTable;
use App\Models\WorkArea;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WorkAreaResource extends BaseResource
{
    protected static ?string $model = WorkArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ksaac;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return WorkAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkAreasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkAreas::route('/'),
            'create' => CreateWorkArea::route('/create'),
            'edit' => EditWorkArea::route('/{record}/edit'),
        ];
    }
}
