<?php

namespace App\Filament\Resources\Partners\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('name')->required()->maxLength(160)->translatableGroup(),
                    Fields::image('logo', __('Logo'))->required(),
                    TextInput::make('url')->label('Website')->url()->maxLength(255),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
