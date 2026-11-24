<?php

namespace App\Filament\Resources\SocialLinks\Schemas;

use App\Enums\Site;
use App\Enums\SocialPlatform;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SocialLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site')->options(Site::class)->default(Site::Ksaac)->required(),
                Select::make('platform')->options(SocialPlatform::class)->required(),
                TextInput::make('url')->label('URL')->url()->required()->maxLength(255)->columnSpanFull(),
                Toggle::make('is_active')->label('Visible on website')->default(true),
            ]);
    }
}
