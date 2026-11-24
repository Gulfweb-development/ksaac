<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Support\Fields;
use App\Settings\KsaacSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/** KSAAC website identity, contact details, app links and footer. */
class KsaacSite extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string $settings = KsaacSettings::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ksaac;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'ksaac/site-settings';

    public function getTitle(): string|Htmlable
    {
        return __('Website settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Website settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))->columns(2)->schema([
                    Fields::translatable(TextInput::make('org_name')->label('Society name')->required()->maxLength(160))->columnSpanFull(),
                    Fields::translatable(TextInput::make('tagline')->maxLength(60))->columnSpanFull(),
                    FileUpload::make('logo')->image()->directory('settings')->helperText(__('Shown in the website header and footer.')),
                    FileUpload::make('favicon')->image()->directory('settings')->maxSize(512)->helperText(__('Browser tab icon: a square PNG, SVG or ICO.')),
                ]),
                Section::make(__('Contact details'))->columns(2)->schema([
                    TextInput::make('phone')->tel()->maxLength(40),
                    TextInput::make('email')->email()->maxLength(255),
                    Fields::translatable(Textarea::make('address')->rows(2)->maxLength(300))->columnSpanFull(),
                ]),
                Section::make(__('Mobile app'))->columns(2)->schema([
                    TextInput::make('ios_app_url')->label('App Store link')->url()->maxLength(255),
                    TextInput::make('android_app_url')->label('Google Play link')->url()->maxLength(255),
                ]),
                Section::make(__('Footer'))->schema([
                    Fields::translatable(Textarea::make('footer_about')->label('About text')->rows(3)->maxLength(400)),
                    Fields::translatable(TextInput::make('copyright')->maxLength(160)),
                ]),
            ]);
    }
}
