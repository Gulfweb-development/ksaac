<?php

namespace App\Filament\Resources\JournalNews;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\JournalNews\Pages\CreateJournalNews;
use App\Filament\Resources\JournalNews\Pages\EditJournalNews;
use App\Filament\Resources\JournalNews\Pages\ListJournalNews;
use App\Filament\Resources\JournalNews\Schemas\JournalNewsForm;
use App\Filament\Resources\JournalNews\Tables\JournalNewsTable;
use App\Models\JournalNews;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JournalNewsResource extends BaseResource
{
    protected static ?string $model = JournalNews::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Jac;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'title';

    protected static function generateDefaultLabel(): string
    {
        return 'Journal News';
    }

    public static function form(Schema $schema): Schema
    {
        return JournalNewsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalNewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalNews::route('/'),
            'create' => CreateJournalNews::route('/create'),
            'edit' => EditJournalNews::route('/{record}/edit'),
        ];
    }
}
