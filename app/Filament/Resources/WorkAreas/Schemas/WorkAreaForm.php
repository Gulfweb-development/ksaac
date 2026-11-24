<?php

namespace App\Filament\Resources\WorkAreas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columnSpanFull()->columns(2)->schema([
                    TextInput::make('title')->required()->maxLength(100)->translatableGroup(),
                    Textarea::make('description')->rows(3)->maxLength(300)->translatableGroup(),
                    Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]);
    }
}
