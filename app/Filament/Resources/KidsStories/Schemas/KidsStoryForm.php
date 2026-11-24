<?php

namespace App\Filament\Resources\KidsStories\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KidsStoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('title')->required()->maxLength(160)->translatableGroup(),
                    Textarea::make('description')->rows(2)->maxLength(300)->translatableGroup(),
                    Fields::image('cover', __('Cover'))->required(),
                    Fields::pdf(),
                    Toggle::make('is_published')->label('Published')->default(true),
                ]),
            ]);
    }
}
