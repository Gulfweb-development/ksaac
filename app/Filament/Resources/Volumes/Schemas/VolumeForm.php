<?php

namespace App\Filament\Resources\Volumes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VolumeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('number')->label('Volume number')->numeric()->required()->minValue(1)->unique(ignoreRecord: true),
                    TextInput::make('year')->numeric()->minValue(1999)->maxValue(now()->year + 1),
                    TextInput::make('title')->required()->maxLength(120)->translatableGroup(),
                ]),
            ]);
    }
}
