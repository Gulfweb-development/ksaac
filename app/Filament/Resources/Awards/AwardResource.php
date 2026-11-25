<?php

namespace App\Filament\Resources\Awards;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Awards\Pages\CreateAward;
use App\Filament\Resources\Awards\Pages\EditAward;
use App\Filament\Resources\Awards\Pages\ListAwards;
use App\Filament\Resources\Awards\Schemas\AwardForm;
use App\Filament\Resources\Awards\Tables\AwardsTable;
use App\Filament\Resources\BaseResource;
use App\Models\Award;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AwardResource extends BaseResource
{
    protected static ?string $model = Award::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Jac;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AwardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AwardsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAwards::route('/'),
            'create' => CreateAward::route('/create'),
            'edit' => EditAward::route('/{record}/edit'),
        ];
    }
}
