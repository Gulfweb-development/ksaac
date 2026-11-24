<?php

namespace App\Filament\Resources\KidsStories;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\KidsStories\Pages\CreateKidsStory;
use App\Filament\Resources\KidsStories\Pages\EditKidsStory;
use App\Filament\Resources\KidsStories\Pages\ListKidsStories;
use App\Filament\Resources\KidsStories\Schemas\KidsStoryForm;
use App\Filament\Resources\KidsStories\Tables\KidsStoriesTable;
use App\Models\KidsStory;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KidsStoryResource extends BaseResource
{
    protected static ?string $model = KidsStory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ksaac;

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return KidsStoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KidsStoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKidsStories::route('/'),
            'create' => CreateKidsStory::route('/create'),
            'edit' => EditKidsStory::route('/{record}/edit'),
        ];
    }
}
