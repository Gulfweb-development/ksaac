<?php

namespace App\Filament\Resources\Pages\Schemas;

use Amzad\FilamentTranslatableGroup\TranslatableGroup;
use App\Enums\Site;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        TranslatableGroup::make()
                            // Fill the slug from the English title while the slug is still empty.
                            ->modifyFieldsUsing(fn (Field $field, string $locale) => $locale !== 'en' ? $field : $field
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (?string $state, Get $get, Set $set) => blank($get('slug')) && $set('slug', Str::slug((string) $state))))
                            ->schema([
                                TextInput::make('title')->required()->maxLength(255),
                            ]),
                        TranslatableGroup::make()
                            ->columns(1)
                            ->schema([
                                RichEditor::make('content')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('pages')
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'underline', 'link'],
                                        ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList'],
                                        ['table', 'attachFiles', 'horizontalRule'],
                                        ['alignStart', 'alignCenter', 'alignEnd'],
                                        ['undo', 'redo'],
                                    ]),
                            ]),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        Select::make('site')->options(Site::class)->default(Site::Ksaac)->required()->live(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->helperText(__('Used in the page address, e.g. ksaac.org/about'))
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('site', $get('site') instanceof Site ? $get('site')->value : $get('site'))),
                        Toggle::make('is_published')->label('Published'),
                    ]),
                    Section::make(__('Image and search'))->schema([
                        SpatieMediaLibraryFileUpload::make('featured')
                            ->label('Featured image')
                            ->collection('featured')
                            ->image()
                            ->imageEditor(),
                        TranslatableGroup::make()
                            ->columns(1)
                            ->schema([
                                Textarea::make('meta_description')
                                    ->label('Search engine description')
                                    ->rows(3)
                                    ->maxLength(160),
                            ]),
                    ]),
                ]),
            ]);
    }
}
