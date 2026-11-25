<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Support\Fields;
use App\Settings\HomepageSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/** KSAAC homepage: which sections show, in what order, with what headings; plus the About and Journal blocks. */
class Homepage extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string $settings = HomepageSettings::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Ksaac;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'ksaac/homepage';

    public function getTitle(): string|Htmlable
    {
        return __('Homepage');
    }

    public static function getNavigationLabel(): string
    {
        return __('Homepage');
    }

    /** Keep exactly the sections the front end can render: add new ones at the end, drop unknown keys. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $saved = collect($data['sections'] ?? [])->keyBy('key')->only(HomepageSettings::SECTIONS);
        $empty = ['ar' => '', 'en' => ''];

        foreach (HomepageSettings::SECTIONS as $key) {
            $saved[$key] ??= ['key' => $key, 'visible' => false, 'kicker' => $empty, 'title' => $empty, 'subtitle' => $empty];
        }

        $data['sections'] = $saved->values()->all();

        return $data;
    }

    public static function sectionLabel(string $key): string
    {
        return match ($key) {
            'hero' => __('Hero slider'),
            'stats' => __('Statistics strip'),
            'about' => __('About the Society'),
            'news' => __('Latest news'),
            'publications' => __('Publications'),
            'journal' => __('Journal of Arab Children'),
            'conferences' => __('Conferences'),
            'project' => __('Featured project'),
            'kids' => __('Kids corner'),
            'partners' => __('Partners'),
            'newsletter' => __('Newsletter signup'),
            default => $key,
        };
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()->columnSpanFull()->tabs([
                    Tab::make('Sections')->icon(Heroicon::OutlinedQueueList)->schema([
                        Repeater::make('sections')
                            ->hiddenLabel()
                            ->helperText(__('Drag to reorder. Turn a section off to hide it from the homepage.'))
                            ->addable(false)
                            ->deletable(false)
                            ->reorderableWithDragAndDrop()
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state) => self::sectionLabel($state['key']).(($state['visible'] ?? true) ? '' : ' — '.__('hidden')))
                            ->schema([
                                Hidden::make('key'),
                                Toggle::make('visible')->label('Show on homepage')->live(),
                                Fields::translatable(TextInput::make('kicker')->label('Small heading')->maxLength(120)),
                                Fields::translatable(TextInput::make('title')->label('Heading')->maxLength(160)),
                                Fields::translatable(Textarea::make('subtitle')->label('Intro text')->rows(2)->maxLength(300)),
                            ]),
                    ]),
                    Tab::make('About block')->icon(Heroicon::OutlinedInformationCircle)->schema([
                        Fields::translatable(Textarea::make('about_body')->label('Text')->rows(5)->maxLength(1200)),
                        Fields::translatable(Textarea::make('about_quote')->label('Quote')->rows(2)->maxLength(300)),
                        Fields::translatable(TextInput::make('about_quote_by')->label('Quote source')->maxLength(80)),
                        Fields::translatable(TextInput::make('about_button_label')->label('Button label')->maxLength(40)),
                        TextInput::make('about_button_url')->label('Button link')->maxLength(255),
                        FileUpload::make('about_images')->label('Photos')->image()->multiple()->maxFiles(2)->reorderable()->directory('homepage'),
                    ]),
                    Tab::make('Journal block')->icon(Heroicon::OutlinedBookOpen)->schema([
                        Fields::translatable(Textarea::make('journal_body')->label('Text')->rows(5)->maxLength(1200)),
                        Fields::translatable(TextInput::make('journal_button_label')->label('Button label')->maxLength(40)),
                        TextInput::make('journal_url')->label('Journal website')->url()->maxLength(255),
                        FileUpload::make('journal_image')->label('Cover image')->image()->directory('homepage'),
                        Repeater::make('journal_tags')
                            ->label('Topic tags')
                            ->columns(2)
                            ->defaultItems(0)
                            ->maxItems(6)
                            ->reorderableWithDragAndDrop()
                            ->schema([
                                TextInput::make('ar')->label('Arabic')->required()->maxLength(60)->extraInputAttributes(['dir' => 'rtl']),
                                TextInput::make('en')->label('English')->required()->maxLength(60),
                            ]),
                    ]),
                ]),
            ]);
    }
}
