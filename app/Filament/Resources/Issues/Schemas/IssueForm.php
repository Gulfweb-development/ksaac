<?php

namespace App\Filament\Resources\Issues\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        Select::make('volume_id')->label('Volume')->relationship('volume', 'number')->getOptionLabelFromRecordUsing(fn ($record) => $record->number.' — '.$record->title)->searchable()->preload()->required(),
                        TextInput::make('number')->label('Issue number')->numeric()->required()->minValue(0)->unique(ignoreRecord: true),
                        TextInput::make('title')->required()->maxLength(160)->translatableGroup(),
                        TextInput::make('theme')->label('Special theme')->maxLength(200)->helperText(__('Optional: the issue\'s featured topic.'))->translatableGroup(),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        DatePicker::make('published_on')->label('Publish date'),
                        Toggle::make('is_published')->label('Published'),
                        Toggle::make('is_current')->label('Current issue')->helperText(__('Only one issue is current; subscribers unlock it. Turning this on turns it off for the previous one.')),
                        Fields::image('cover', __('Cover')),
                        Fields::pdf('file', __('Full issue PDF'), private: true),
                    ]),
                ]),
            ]);
    }
}
