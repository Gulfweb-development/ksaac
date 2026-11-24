<?php

namespace App\Filament\Resources\Publications\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        ...Fields::titleAndSlug(),
                        TextInput::make('author')->label('Author or editor')->maxLength(255)->translatableGroup(),
                        Fields::richText('description', 'publications'),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        Select::make('publication_category_id')->label('Category')->relationship('category', 'slug')->getOptionLabelFromRecordUsing(fn ($record) => $record->name)->preload(),
                        TextInput::make('year')->numeric()->minValue(1980)->maxValue(now()->year + 1),
                        TextInput::make('purchase_url')->label('Purchase link')->url()->maxLength(255),
                        Toggle::make('is_published')->label('Published'),
                        Toggle::make('is_featured')->label('Feature on homepage'),
                        Fields::image('cover', __('Cover')),
                        Fields::pdf(),
                    ]),
                ]),
            ]);
    }
}
