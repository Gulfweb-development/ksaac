<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\ReviewStatus;
use App\Enums\SubmissionStatus;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\SubmissionReview;
use App\Services\Journal\PeerReview;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

/** Workflow buttons on the submission page; each shows only when the step is possible. */
class SubmissionActions
{
    public static function startScreening(): Action
    {
        return Action::make('startScreening')
            ->label(__('Start initial check'))
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->visible(fn (Submission $record) => $record->status === SubmissionStatus::Received)
            ->requiresConfirmation()
            ->action(fn (Submission $record) => self::run(fn () => app(PeerReview::class)->startScreening($record, auth('admin')->user()), __('Initial check started')));
    }

    public static function sendToReviewers(): Action
    {
        return Action::make('sendToReviewers')
            ->label(fn (Submission $record) => $record->status === SubmissionStatus::UnderReview ? __('Add reviewer') : __('Send to reviewers'))
            ->icon(Heroicon::OutlinedUserPlus)
            ->color('info')
            ->visible(fn (Submission $record) => in_array($record->status, [SubmissionStatus::Received, SubmissionStatus::Screening, SubmissionStatus::UnderReview]))
            ->modalDescription(__('Reviewers receive an email and see only the anonymised manuscript. The journal\'s rules call for two reviewers.'))
            ->schema(fn (Submission $record) => [
                Select::make('reviewers')
                    ->label('Reviewers')
                    ->multiple()
                    ->required()
                    ->options(app(PeerReview::class)->reviewerPool()
                        ->whereNotIn('id', $record->reviews()->where('round', $record->round)->pluck('reviewer_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->helperText(__('Only admins with the Reviewer role are listed.')),
                DatePicker::make('due_on')->label('Review due by')->default(now()->addWeeks(3))->required()->minDate(today()),
            ])
            ->action(fn (Submission $record, array $data) => self::run(
                fn () => app(PeerReview::class)->assignReviewers($record, $data['reviewers'], Carbon::parse($data['due_on']), auth('admin')->user()),
                __('Reviewers invited'),
            ));
    }

    public static function decide(): Action
    {
        return Action::make('decide')
            ->label(__('Record decision'))
            ->icon(Heroicon::OutlinedScale)
            ->color('success')
            ->visible(fn (Submission $record) => in_array($record->status, [SubmissionStatus::Screening, SubmissionStatus::UnderReview, SubmissionStatus::RevisionsRequested]))
            ->modalDescription(fn (Submission $record) => self::reviewSummary($record))
            ->schema(fn (Submission $record) => [
                Radio::make('decision')
                    ->options([
                        SubmissionStatus::Accepted->value => SubmissionStatus::Accepted->getLabel(),
                        SubmissionStatus::RevisionsRequested->value => SubmissionStatus::RevisionsRequested->getLabel(),
                        SubmissionStatus::Rejected->value => SubmissionStatus::Rejected->getLabel(),
                    ])
                    ->required(),
                Textarea::make('comments')
                    ->label('Comments sent to the researcher')
                    ->helperText(__('Prefilled with the reviewers\' comments to the author. Reviewer names are never sent.'))
                    ->rows(8)
                    ->default(self::commentsToAuthor($record)),
            ])
            ->action(fn (Submission $record, array $data) => self::run(
                fn () => app(PeerReview::class)->decide($record, SubmissionStatus::from($data['decision']), $data['comments'] ?? null, auth('admin')->user()),
                __('Decision recorded and the researcher emailed'),
            ));
    }

    public static function startRevisionRound(): Action
    {
        return Action::make('startRevisionRound')
            ->label(__('Start review of revised version'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (Submission $record) => $record->status === SubmissionStatus::RevisionsRequested)
            ->requiresConfirmation()
            ->modalDescription(__('Upload the revised and anonymised files first (Edit). The reviewers of the last round are invited again.'))
            ->action(fn (Submission $record) => self::run(fn () => app(PeerReview::class)->startRevisionRound($record, auth('admin')->user()), __('New review round started')));
    }

    public static function publish(): Action
    {
        return Action::make('publish')
            ->label(__('Publish in an issue'))
            ->icon(Heroicon::OutlinedBookOpen)
            ->color('success')
            ->visible(fn (Submission $record) => $record->status === SubmissionStatus::Accepted)
            ->modalDescription(__('Creates an unpublished article in the issue from this paper\'s details and newest manuscript. Check it, then publish it from Articles.'))
            ->schema([
                Select::make('issue_id')
                    ->label('Issue')
                    ->options(fn () => Issue::orderByDesc('number')->get()->mapWithKeys(fn (Issue $i) => [$i->id => __('Issue').' '.$i->number.' — '.$i->title]))
                    ->searchable()
                    ->required(),
            ])
            ->action(fn (Submission $record, array $data) => self::run(
                fn () => app(PeerReview::class)->publish($record, Issue::findOrFail($data['issue_id']), auth('admin')->user()),
                __('Article created in the issue'),
            ));
    }

    public static function recordReward(): Action
    {
        return Action::make('recordReward')
            ->label(__('Record reward payment'))
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('gray')
            ->visible(fn (Submission $record) => in_array($record->status, [SubmissionStatus::Accepted, SubmissionStatus::Published]) && ! $record->reward_paid_on)
            ->fillForm(fn (Submission $record) => ['reward_amount' => $record->reward_amount ?? PeerReview::DEFAULT_REWARD, 'reward_paid_on' => today()])
            ->schema([
                TextInput::make('reward_amount')->label('Amount (KWD)')->numeric()->required()->minValue(0),
                DatePicker::make('reward_paid_on')->label('Paid on')->required(),
            ])
            ->action(function (Submission $record, array $data) {
                $record->update($data);
                activity('submission')->performedOn($record)->causedBy(auth('admin')->user())->log(__('Reward of :amount KWD paid', ['amount' => $data['reward_amount']]));
                Notification::make()->success()->title(__('Reward payment recorded'))->send();
            });
    }

    public static function withdraw(): Action
    {
        return Action::make('withdraw')
            ->label(__('Withdraw'))
            ->icon(Heroicon::OutlinedArchiveBoxXMark)
            ->color('danger')
            ->visible(fn (Submission $record) => ! $record->status->isClosed())
            ->schema([Textarea::make('reason')->label('Reason (sent to the researcher)')->rows(3)])
            ->action(fn (Submission $record, array $data) => self::run(fn () => app(PeerReview::class)->withdraw($record, auth('admin')->user(), $data['reason'] ?? null), __('Submission withdrawn')));
    }

    /** Reviewers' comments to the author, unnamed, for the decision email. */
    private static function commentsToAuthor(Submission $record): ?string
    {
        return $record->reviews()->where('round', $record->round)->where('status', ReviewStatus::Completed)->get()
            ->filter(fn (SubmissionReview $r) => filled($r->comments_to_author))
            ->values()
            ->map(fn (SubmissionReview $r, int $i) => __('Reviewer :n', ['n' => $i + 1]).":\n".$r->comments_to_author)
            ->join("\n\n") ?: null;
    }

    private static function reviewSummary(Submission $record): string
    {
        $reviews = $record->reviews()->where('round', $record->round)->get();
        $done = $reviews->where('status', ReviewStatus::Completed);

        if ($reviews->isEmpty()) {
            return __('No reviews yet.');
        }

        return __(':done of :total reviews complete', ['done' => $done->count(), 'total' => $reviews->where('status', '!=', ReviewStatus::Declined)->count()]).': '
            .$done->map(fn (SubmissionReview $r) => $r->recommendation->getLabel())->join(' · ');
    }

    private static function run(callable $step, string $success): void
    {
        try {
            $step();
            Notification::make()->success()->title($success)->send();
        } catch (DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }
}
