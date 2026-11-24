<?php

namespace App\Filament\Resources\EditorialMembers\Schemas;

use App\Enums\EditorialGroup;
use App\Filament\Support\Fields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EditorialMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    Select::make('group')->label('Board')->options(EditorialGroup::class)->required(),
                    TextInput::make('name')->maxLength(160)->translatableGroup(modifyFieldsUsing: fn (Field $field, string $locale) => $field->required($locale === 'ar')),
                    TextInput::make('role')->label('Role on the board')->maxLength(120)->helperText(__('e.g. Chair, Editor-in-chief. Leave empty for members.'))->translatableGroup(),
                    Fields::image('photo', __('Photo')),
                    Toggle::make('is_deceased')->label('Deceased')->live(),
                    DatePicker::make('deceased_on')->label('Date of death')->visible(fn (Get $get) => $get('is_deceased')),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
