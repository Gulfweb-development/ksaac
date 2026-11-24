<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleType;
use App\Filament\Support\Fields;
use App\Models\Issue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Content'))->schema([
                        Select::make('issue_id')->label('Issue')->relationship('issue', 'number', fn ($query) => $query->with('volume')->orderByDesc('number'))->getOptionLabelFromRecordUsing(fn (Issue $record) => __('Issue').' '.$record->number.' — '.$record->volume?->title)->searchable()->preload()->required()
                            // Arriving from an issue's article list preselects that issue.
                            ->default(fn () => request()->integer('issue') ?: null),
                        ...Fields::titleAndSlug(),
                        Repeater::make('authors')
                            ->label('Authors')
                            ->schema([
                                TextInput::make('name')->maxLength(160)->translatableGroup(),
                                TextInput::make('affiliation')->label('Affiliation')->maxLength(255)->translatableGroup(),
                            ])
                            ->itemLabel(fn (array $state) => ($state['name']['ar'] ?? null) ?: ($state['name']['en'] ?? null))
                            ->defaultItems(1)
                            ->reorderableWithDragAndDrop()
                            ->collapsible()
                            ->columnSpanFull(),
                        Textarea::make('abstract')->rows(6)->maxLength(5000)->translatableGroup(),
                        TextInput::make('keywords')->maxLength(500)->helperText(__('Separate keywords with commas.'))->translatableGroup(),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Publishing'))->schema([
                        Select::make('type')->options(ArticleType::class)->default(ArticleType::FieldResearch)->required(),
                        Select::make('language')->label('Article language')->options(config('app.locale_labels'))->default('ar')->required(),
                        TextInput::make('page_from')->label('First page')->numeric()->minValue(1),
                        TextInput::make('page_to')->label('Last page')->numeric()->gte('page_from'),
                        TextInput::make('doi')->label('DOI')->maxLength(255)->placeholder('10.xxxx/xxxxx'),
                        Toggle::make('is_published')->label('Published')->default(true),
                        Fields::pdf(private: true),
                    ]),
                ]),
            ]);
    }
}
