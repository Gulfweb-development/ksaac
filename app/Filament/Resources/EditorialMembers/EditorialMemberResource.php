<?php

namespace App\Filament\Resources\EditorialMembers;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EditorialMembers\Pages\CreateEditorialMember;
use App\Filament\Resources\EditorialMembers\Pages\EditEditorialMember;
use App\Filament\Resources\EditorialMembers\Pages\ListEditorialMembers;
use App\Filament\Resources\EditorialMembers\Schemas\EditorialMemberForm;
use App\Filament\Resources\EditorialMembers\Tables\EditorialMembersTable;
use App\Models\EditorialMember;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EditorialMemberResource extends BaseResource
{
    protected static ?string $model = EditorialMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Jac;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EditorialMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EditorialMembersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEditorialMembers::route('/'),
            'create' => CreateEditorialMember::route('/create'),
            'edit' => EditEditorialMember::route('/{record}/edit'),
        ];
    }
}
