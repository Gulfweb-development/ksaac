<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('title')->required()->maxLength(160)->translatableGroup(),
                    TextInput::make('youtube_url')->label('YouTube link')->url()->required()->maxLength(255)->regex('~(youtu\.be/|youtube\.com/)~')->validationMessages(['regex' => __('Use a YouTube link.')])->columnSpanFull(),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
