<?php

namespace App\Filament\Resources\News\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        ...Fields::titleAndSlug(),
                        Textarea::make('excerpt')->label('Summary')->rows(3)->maxLength(400)->translatableGroup(),
                        Fields::richText('body', 'news'),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        DateTimePicker::make('published_at')->label('Publish date')->default(now())->required(),
                        Toggle::make('is_published')->label('Published'),
                        Toggle::make('is_featured')->label('Feature on homepage'),
                        Fields::image(),
                        Fields::image('gallery', __('Gallery'))->multiple()->reorderable(),
                    ]),
                ]),
            ]);
    }
}
