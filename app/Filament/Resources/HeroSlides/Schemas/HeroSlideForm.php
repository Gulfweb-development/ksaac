<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    Fields::image()->required()->columnSpanFull(),
                    TextInput::make('kicker')->label('Small heading')->maxLength(120)->translatableGroup(),
                    TextInput::make('title')->required()->maxLength(160)->translatableGroup(),
                    Textarea::make('subtitle')->rows(2)->maxLength(300)->translatableGroup(),
                    TextInput::make('button_label')->maxLength(40)->translatableGroup(),
                    TextInput::make('button_url')->label('Button link')->maxLength(255),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
