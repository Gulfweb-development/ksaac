<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Support\Fields;
use App\Settings\JacSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/** Journal website identity, contact details, app links, footer and bank accounts for subscription payments. */
class JacSite extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string $settings = JacSettings::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Jac;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'jac/site-settings';

    public function getTitle(): string|Htmlable
    {
        return __('Journal website settings');
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
                    Fields::translatable(TextInput::make('name')->label('Journal name')->required()->maxLength(160)),
                    FileUpload::make('logo')->image()->directory('settings')->helperText(__('Shown in the website header and footer.')),
                ]),
                Section::make(__('Contact details'))->columns(3)->schema([
                    TextInput::make('phone')->maxLength(80),
                    TextInput::make('fax')->maxLength(40),
                    TextInput::make('email')->email()->maxLength(255),
                    Fields::translatable(Textarea::make('address')->rows(2)->maxLength(300)),
                    Fields::translatable(TextInput::make('po_box')->label('P.O. Box')->maxLength(120)),
                ]),
                Section::make(__('Mobile app'))->columns(2)->schema([
                    TextInput::make('ios_app_url')->label('App Store link')->url()->maxLength(255),
                    TextInput::make('android_app_url')->label('Google Play link')->url()->maxLength(255),
                ]),
                Section::make(__('Bank accounts'))
                    ->description(__('Shown on the subscription form so subscribers can pay by bank transfer.'))
                    ->schema([
                        Fields::translatable(TextInput::make('bank_name')->label('Bank')->maxLength(160)),
                        Fields::translatable(TextInput::make('account_holder')->label('Account holder')->maxLength(160)),
                        Repeater::make('bank_accounts')
                            ->label('Accounts')
                            ->columns(4)
                            ->defaultItems(0)
                            ->reorderableWithDragAndDrop()
                            ->itemLabel(fn (array $state) => $state['currency'] ?? null)
                            ->schema([
                                Select::make('currency')->options(['KWD' => 'KWD', 'USD' => 'USD', 'EUR' => 'EUR'])->required(),
                                TextInput::make('account_number')->label('Account number')->required()->maxLength(40),
                                TextInput::make('swift')->label('SWIFT')->maxLength(11),
                                TextInput::make('iban')->label('IBAN')->maxLength(42),
                            ]),
                    ]),
                Section::make(__('Footer'))->schema([
                    Fields::translatable(Textarea::make('footer_about')->label('About text')->rows(3)->maxLength(400)),
                    Fields::translatable(TextInput::make('copyright')->maxLength(160)),
                ]),
            ]);
    }
}
