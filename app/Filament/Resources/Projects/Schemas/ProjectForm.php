<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        ...Fields::titleAndSlug(),
                        Textarea::make('summary')->rows(3)->maxLength(400)->translatableGroup(),
                        Fields::richText('body', 'projects'),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        DatePicker::make('started_on')->label('Start date'),
                        Toggle::make('is_published')->label('Published'),
                        Toggle::make('is_featured')->label('Feature on homepage')->helperText(__('The newest featured project fills the homepage project section.')),
                        Fields::image(),
                    ]),
                ]),
            ]);
    }
}
