<?php

namespace App\Filament\Resources\Submissions\Schemas;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class SubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Paper'))->schema([
                        TextEntry::make('title')->weight('bold')->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('abstract')->prose()->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('keywords')->placeholder('—')->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('theme')->label('Call-for-papers theme')->placeholder('—')->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('co_authors')
                            ->label('Co-authors')
                            ->state(fn (Submission $record) => collect($record->co_authors)->map(fn ($a) => e($a['name']).($a['affiliation'] ?? null ? ' — '.e($a['affiliation']) : ''))->join('<br>') ?: null)
                            ->html()
                            ->placeholder('—'),
                    ]),
                    Section::make(__('Files'))->columns(3)->schema([
                        self::files('manuscript', __('Manuscript (with author names)')),
                        self::files('blind_manuscript', __('Anonymised manuscript (for reviewers)')),
                        self::files('attachments', __('Other attachments')),
                    ]),
                    Section::make(__('History'))->collapsible()->schema([
                        TextEntry::make('history')
                            ->hiddenLabel()
                            // Workflow events only (the panel's own model logger writes other rows); id order is exact within a second.
                            ->state(fn (Submission $record) => $record->activities()->where('log_name', 'submission')->with('causer')->latest('id')->get()->map(fn (Activity $a) => self::historyLine($a))->join('<br>') ?: null)
                            ->html()
                            ->placeholder(__('No history yet.')),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Status'))->schema([
                        TextEntry::make('reference')->copyable()->weight('bold'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('round')->label('Review round'),
                        TextEntry::make('editor.name')->label('Handling editor')->placeholder('—'),
                        TextEntry::make('created_at')->label('Received on')->dateTime(),
                        TextEntry::make('decided_at')->label('Decision date')->dateTime()->placeholder('—'),
                        TextEntry::make('decision_note')->label('Comments sent to the researcher')->placeholder('—')->extraAttributes(['dir' => 'auto']),
                    ]),
                    Section::make(__('Researcher'))->schema([
                        TextEntry::make('researcher.name')->label('Name'),
                        TextEntry::make('researcher.email')->label('Email')->copyable(),
                        TextEntry::make('researcher.mobile')->label('Mobile')->placeholder('—'),
                        TextEntry::make('language')->label('Paper language')->formatStateUsing(fn ($state) => config("app.locale_labels.$state", $state)),
                    ]),
                    Section::make(__('Publication and reward'))
                        ->visible(fn (Submission $record) => in_array($record->status, [SubmissionStatus::Accepted, SubmissionStatus::Published]))
                        ->schema([
                            TextEntry::make('issue.number')->label('Published in issue')->placeholder('—'),
                            TextEntry::make('reward_amount')->label('Reward (KWD)')->placeholder('—'),
                            TextEntry::make('reward_paid_on')->label('Reward paid on')->date()->placeholder(__('Not paid yet')),
                        ]),
                ]),
            ]);
    }

    /** Download links for one private media collection (signed, short-lived). */
    private static function files(string $collection, string $label): TextEntry
    {
        return TextEntry::make($collection.'_files')
            ->label($label)
            ->state(fn (Submission $record) => $record->getMedia($collection)
                ->map(fn ($media) => '<a class="underline" target="_blank" href="'.e($media->getTemporaryUrl(now()->addMinutes(30))).'">'.e($media->file_name).'</a> <span style="opacity:.6">· '.$media->created_at->format('Y-m-d').'</span>')
                ->join('<br>') ?: null)
            ->html()
            ->placeholder('—');
    }

    private static function historyLine(Activity $activity): string
    {
        $changed = $activity->properties['attributes'] ?? [];
        $text = match (true) {
            isset($changed['status']) => __('Status changed to :status', ['status' => SubmissionStatus::from($changed['status'])->getLabel()]),
            isset($changed['round']) => __('Review round :n started', ['n' => $changed['round']]),
            $activity->description === 'created' => __('Submission received'),
            default => $activity->description,
        };

        return '<span style="opacity:.6">'.$activity->created_at->format('Y-m-d H:i').'</span> — '.e($text).($activity->causer ? ' <span style="opacity:.6">('.e($activity->causer->name).')</span>' : '');
    }
}
