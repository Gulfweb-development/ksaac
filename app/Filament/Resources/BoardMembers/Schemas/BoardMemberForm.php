<?php

namespace App\Filament\Resources\BoardMembers\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoardMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('name')->required()->maxLength(160)->translatableGroup(),
                    TextInput::make('position')->maxLength(120)->translatableGroup(),
                    Fields::image('photo', __('Photo')),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
