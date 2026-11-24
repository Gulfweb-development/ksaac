<?php

namespace App\Filament\Resources\Stats\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('value')->numeric()->required()->minValue(0),
                    TextInput::make('suffix')->maxLength(10)->helperText(__('e.g. + for "100+"')),
                    TextInput::make('label')->required()->maxLength(60)->translatableGroup(),
                    Toggle::make('animate')->label('Count up when shown')->helperText(__('Turn off for years such as 1980.'))->default(true),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
