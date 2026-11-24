<?php

namespace App\Filament\Resources\Conferences;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Conferences\Pages\CreateConference;
use App\Filament\Resources\Conferences\Pages\EditConference;
use App\Filament\Resources\Conferences\Pages\ListConferences;
use App\Filament\Resources\Conferences\Schemas\ConferenceForm;
use App\Filament\Resources\Conferences\Tables\ConferencesTable;
use App\Models\Conference;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConferenceResource extends BaseResource
{
    protected static ?string $model = Conference::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ksaac;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ConferenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConferencesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConferences::route('/'),
            'create' => CreateConference::route('/create'),
            'edit' => EditConference::route('/{record}/edit'),
        ];
    }
}
