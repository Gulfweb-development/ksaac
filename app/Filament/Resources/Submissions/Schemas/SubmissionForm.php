<?php

namespace App\Filament\Resources\Submissions\Schemas;

use App\Enums\SubmissionStatus;
use App\Models\Admin;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        $documents = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Paper'))->schema([
                        TextInput::make('title')->required()->maxLength(500)->extraInputAttributes(['dir' => 'auto']),
                        Textarea::make('abstract')->required()->rows(6)->maxLength(5000)->extraInputAttributes(['dir' => 'auto']),
                        TextInput::make('keywords')->maxLength(500)->helperText(__('Separate keywords with commas.'))->extraInputAttributes(['dir' => 'auto']),
                        TextInput::make('theme')->label('Call-for-papers theme')->maxLength(255)->helperText(__('Optional: which journal theme the paper answers.')),
                        Repeater::make('co_authors')
                            ->label('Co-authors')
                            ->columns(3)
                            ->defaultItems(0)
                            ->reorderableWithDragAndDrop()
                            ->schema([
                                TextInput::make('name')->required()->maxLength(160),
                                TextInput::make('affiliation')->label('Affiliation')->maxLength(255),
                                TextInput::make('email')->email()->maxLength(255),
                            ]),
                    ]),
                    Section::make(__('Files'))->schema([
                        SpatieMediaLibraryFileUpload::make('manuscript')
                            ->label('Manuscript (with author names)')
                            ->helperText(__('Every version is kept; the newest is used when the paper is published.'))
                            ->collection('manuscript')
                            ->multiple()
                            ->acceptedFileTypes($documents)
                            ->maxSize(51200)
                            ->downloadable()
                            ->disk('local')
                            ->visibility('private')
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('blind_manuscript')
                            ->label('Anonymised manuscript (for reviewers)')
                            ->helperText(__('Remove author names and affiliations. Reviewers only ever see this file.'))
                            ->collection('blind_manuscript')
                            ->multiple()
                            ->acceptedFileTypes($documents)
                            ->maxSize(51200)
                            ->downloadable()
                            ->disk('local')
                            ->visibility('private'),
                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->label('Other attachments')
                            ->helperText(__('e.g. CV, signed originality pledge, Arabic and English summaries.'))
                            ->collection('attachments')
                            ->multiple()
                            ->maxSize(51200)
                            ->downloadable()
                            ->disk('local')
                            ->visibility('private'),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Researcher'))->schema([
                        Select::make('user_id')
                            ->label('Researcher account')
                            ->relationship('researcher', 'name')
                            ->getOptionLabelFromRecordUsing(fn (User $user) => $user->name.' — '.$user->email)
                            ->searchable(['name', 'email'])
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('email')->email()->required()->unique(User::class, 'email'),
                                TextInput::make('mobile')->tel()->maxLength(40),
                            ])
                            // The researcher sets a password later via "Forgot password" on the journal site.
                            ->createOptionUsing(fn (array $data) => User::create($data + ['password' => Str::password(32)])->getKey()),
                        Select::make('language')->label('Paper language')->options(config('app.locale_labels'))->default('ar')->required(),
                        Select::make('editor_id')
                            ->label('Handling editor')
                            ->options(fn () => Admin::orderBy('name')->pluck('name', 'id'))
                            ->default(fn () => auth('admin')->id())
                            ->searchable(),
                    ]),
                    Section::make(__('Author reward'))
                        ->visible(fn (Get $get, $record) => in_array($record?->status, [SubmissionStatus::Accepted, SubmissionStatus::Published]))
                        ->schema([
                            TextInput::make('reward_amount')->label('Amount (KWD)')->numeric()->minValue(0),
                            DatePicker::make('reward_paid_on')->label('Paid on'),
                        ]),
                ]),
            ]);
    }
}
