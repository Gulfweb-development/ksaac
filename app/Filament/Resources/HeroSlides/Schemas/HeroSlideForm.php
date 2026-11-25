<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use App\Enums\Site;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
                    Select::make('site')->options(Site::class)->default(Site::Ksaac)->required(),
                    Fields::image(label: __('Cover image'))->required()
                        ->helperText(__('Shown on its own, or before the video loads.')),
                    SpatieMediaLibraryFileUpload::make('video')
                        ->label(__('Background video (optional)'))
                        ->collection('video')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->maxSize(51200)
                        ->helperText(__('MP4 or WebM, up to 50 MB. Plays muted on a loop.')),
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
