<?php

namespace App\Filament\Resources\Conferences\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConferenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        ...Fields::titleAndSlug(),
                        TextInput::make('venue')->maxLength(255)->translatableGroup(),
                        Textarea::make('summary')->rows(3)->maxLength(400)->translatableGroup(),
                        Fields::richText('body', 'conferences'),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        DatePicker::make('starts_on')->label('Start date'),
                        DatePicker::make('ends_on')->label('End date')->afterOrEqual('starts_on'),
                        Toggle::make('is_published')->label('Published'),
                        Fields::image(),
                        Fields::image('gallery', __('Gallery'))->multiple()->reorderable(),
                        SpatieMediaLibraryFileUpload::make('files')->label('Documents')->collection('files')->multiple()->downloadable()->maxSize(51200),
                    ]),
                ]),
            ]);
    }
}
