<?php

namespace App\Filament\Resources\PublicationCategories\Schemas;

use App\Filament\Support\Fields;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicationCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    ...Fields::titleAndSlug('name'),
                ]),
            ]);
    }
}
