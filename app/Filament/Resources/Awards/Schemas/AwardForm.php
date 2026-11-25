<?php

namespace App\Filament\Resources\Awards\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AwardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    Fields::image(label: __('Image or logo'))->required()->columnSpanFull(),
                    TextInput::make('title')->required()->maxLength(160)->translatableGroup(),
                    Textarea::make('description')->rows(2)->maxLength(500)->translatableGroup(),
                    TextInput::make('year')->numeric()->minValue(1900)->maxValue(2100),
                    TextInput::make('url')->label('Link')->url()->maxLength(255),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
