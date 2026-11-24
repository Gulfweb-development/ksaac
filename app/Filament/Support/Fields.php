<?php

namespace App\Filament\Support;

use Amzad\FilamentTranslatableGroup\TranslatableGroup;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/** Form building blocks reused across content resources. */
class Fields
{
    /** Arabic + English copies of one field, side by side (or stacked with $columns = 1). */
    public static function translatable(Field $field, int $columns = 2): TranslatableGroup
    {
        return TranslatableGroup::make()->columns($columns)->schema([$field]);
    }

    /** Bilingual title plus a slug that fills from the English title while empty. */
    public static function titleAndSlug(string $title = 'title'): array
    {
        return [
            TranslatableGroup::make()
                ->modifyFieldsUsing(fn (Field $field, string $locale) => $locale !== 'en' ? $field : $field
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, Get $get, Set $set) => blank($get('slug')) && $set('slug', Str::slug((string) $state))))
                ->schema([TextInput::make($title)->required()->maxLength(255)]),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->helperText(__('Used in the page address.')),
        ];
    }

    /** Bilingual rich-text body with image/file attachments. */
    public static function richText(string $name = 'body', string $directory = 'content'): TranslatableGroup
    {
        return self::translatable(
            RichEditor::make($name)
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory($directory)
                ->toolbarButtons([
                    ['bold', 'italic', 'underline', 'link'],
                    ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList'],
                    ['table', 'attachFiles', 'horizontalRule'],
                    ['alignStart', 'alignCenter', 'alignEnd'],
                    ['undo', 'redo'],
                ]),
            columns: 1,
        );
    }

    public static function image(string $collection = 'image', ?string $label = null): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($collection)
            ->label($label ?? __('Image'))
            ->collection($collection)
            ->image()
            ->imageEditor()
            ->maxSize(5120);
    }

    public static function pdf(string $collection = 'file', ?string $label = null): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($collection)
            ->label($label ?? __('PDF file'))
            ->collection($collection)
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(51200)
            ->downloadable();
    }
}
