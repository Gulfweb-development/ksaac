<?php

namespace App\Services\Journal;

use App\Enums\ArticleType;
use App\Enums\ReviewStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\SubmissionReview;
use App\Notifications\ReviewInvitation;
use App\Notifications\SubmissionUpdate;
use Carbon\CarbonInterface;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * The journal's peer-review workflow. Rules follow the publishing rules on jac-kw.org:
 * two confidential reviewers per paper; if they disagree, a third reviewer decides.
 */
class PeerReview
{
    public const REVIEWER_ROLE = 'reviewer';

    public const DEFAULT_REWARD = 150;

    public function startScreening(Submission $submission, ?Admin $by = null): void
    {
        $this->requireStatus($submission, [SubmissionStatus::Received]);
        $this->setStatus($submission, SubmissionStatus::Screening, $by);
        $submission->update(['editor_id' => $submission->editor_id ?? $by?->getKey()]);
    }

    /** Invite reviewers for the current round. Reviewers see only the anonymised manuscript. */
    public function assignReviewers(Submission $submission, iterable $reviewerIds, ?CarbonInterface $dueOn = null, ?Admin $by = null): Collection
    {
        // RevisionsRequested: a revised version is being sent back to reviewers (new round).
        $this->requireStatus($submission, [SubmissionStatus::Received, SubmissionStatus::Screening, SubmissionStatus::UnderReview, SubmissionStatus::RevisionsRequested]);

        if (! $submission->getMedia('blind_manuscript')->count()) {
            throw new DomainException(__('Upload the anonymised manuscript before sending the paper to reviewers.'));
        }

        $created = DB::transaction(function () use ($submission, $reviewerIds, $dueOn, $by) {
            $created = collect();

            foreach ($reviewerIds as $reviewerId) {
                $review = SubmissionReview::firstOrCreate(
                    ['submission_id' => $submission->id, 'reviewer_id' => $reviewerId, 'round' => $submission->round],
                    ['due_on' => $dueOn ?? now()->addWeeks(3)],
                );

                if ($review->wasRecentlyCreated) {
                    $created->push($review);
                }
            }

            if ($submission->status !== SubmissionStatus::UnderReview) {
                $this->setStatus($submission, SubmissionStatus::UnderReview, $by);
            }

            return $created;
        });

        $created->each(function (SubmissionReview $review) use ($submission) {
            $review->reviewer->notify(new ReviewInvitation($review));
            $this->log($submission, __(':reviewer invited to review (round :n)', ['reviewer' => $review->reviewer->name, 'n' => $review->round]));
        });

        return $created;
    }

    public function respond(SubmissionReview $review, bool $accept): void
    {
        if ($review->status !== ReviewStatus::Invited) {
            throw new DomainException(__('This invitation has already been answered.'));
        }

        $review->update(['status' => $accept ? ReviewStatus::Accepted : ReviewStatus::Declined, 'responded_at' => now()]);

        $this->log($review->submission, __($accept ? ':reviewer accepted the invitation' : ':reviewer declined the invitation', ['reviewer' => $review->reviewer->name]));

        if (! $accept) {
            $this->alertEditors($review->submission, __(':reviewer declined to review :reference.', ['reviewer' => $review->reviewer->name, 'reference' => $review->submission->reference]));
        }
    }

    /** Save the reviewer's report; may trigger a third reviewer when the first two disagree. */
    public function complete(SubmissionReview $review, array $report): ?SubmissionReview
    {
        if (! in_array($review->status, [ReviewStatus::Invited, ReviewStatus::Accepted])) {
            throw new DomainException(__('This review can no longer be changed.'));
        }

        $review->update([
            'scores' => $report['scores'] ?? null,
            'recommendation' => $report['recommendation'],
            'comments_to_author' => $report['comments_to_author'] ?? null,
            'comments_to_editor' => $report['comments_to_editor'] ?? null,
            'status' => ReviewStatus::Completed,
            'responded_at' => $review->responded_at ?? now(),
            'completed_at' => now(),
        ]);

        $submission = $review->submission;
        $this->log($submission, __('Review received from :reviewer: :recommendation', ['reviewer' => $review->reviewer->name, 'recommendation' => $review->recommendation->getLabel()]));
        $this->alertEditors($submission, __('A review of :reference is complete.', ['reference' => $submission->reference]));

        return $this->assignThirdReviewerIfSplit($submission);
    }

    /**
     * Two completed reviews pointing opposite ways (accept vs reject) and no third reviewer yet:
     * invite the reviewer with the fewest open reviews who has not seen this paper.
     */
    public function assignThirdReviewerIfSplit(Submission $submission): ?SubmissionReview
    {
        $reviews = $submission->currentReviews()->get();
        $leanings = $reviews->where('status', ReviewStatus::Completed)->map(fn (SubmissionReview $r) => $r->recommendation->leaning());

        if ($reviews->count() !== 2 || $leanings->count() !== 2 || ! ($leanings->contains(1) && $leanings->contains(-1))) {
            return null;
        }

        $already = $submission->reviews()->pluck('reviewer_id');
        $candidate = $this->reviewerPool()
            ->whereNotIn('id', $already)
            ->withCount(['reviewAssignments as open_reviews_count' => fn ($q) => $q->whereIn('status', [ReviewStatus::Invited, ReviewStatus::Accepted])])
            ->orderBy('open_reviews_count')
            ->first();

        if (! $candidate) {
            $this->alertEditors($submission, __('The reviewers disagree on :reference and no other reviewer is available. Please assign a third reviewer.', ['reference' => $submission->reference]));

            return null;
        }

        $third = $this->assignReviewers($submission, [$candidate->id])->first();
        $this->log($submission, __('Reviewers disagreed; :reviewer was invited as third reviewer.', ['reviewer' => $candidate->name]));
        $this->alertEditors($submission, __('The reviewers disagree on :reference. :reviewer was invited as third reviewer.', ['reference' => $submission->reference, 'reviewer' => $candidate->name]));

        return $third;
    }

    /** Editor's decision after review: Accepted, RevisionsRequested or Rejected. */
    public function decide(Submission $submission, SubmissionStatus $decision, ?string $commentsToAuthor = null, ?Admin $by = null): void
    {
        if (! in_array($decision, [SubmissionStatus::Accepted, SubmissionStatus::RevisionsRequested, SubmissionStatus::Rejected])) {
            throw new DomainException('Not a decision: '.$decision->value);
        }

        $this->requireStatus($submission, [SubmissionStatus::Screening, SubmissionStatus::UnderReview, SubmissionStatus::RevisionsRequested]);

        $submission->fill([
            'decision_note' => $commentsToAuthor,
            'decided_at' => now(),
            'reward_amount' => $decision === SubmissionStatus::Accepted ? ($submission->reward_amount ?? self::DEFAULT_REWARD) : $submission->reward_amount,
        ]);
        $this->setStatus($submission, $decision, $by, $commentsToAuthor);
    }

    /** The researcher sent a revised version: start a new round with the reviewers who completed the last one. */
    public function startRevisionRound(Submission $submission, ?Admin $by = null): Collection
    {
        $this->requireStatus($submission, [SubmissionStatus::RevisionsRequested]);

        $previous = $submission->reviews()->where('round', $submission->round)->where('status', ReviewStatus::Completed)->pluck('reviewer_id');
        $submission->update(['round' => $submission->round + 1]);

        return $this->assignReviewers($submission, $previous, by: $by);
    }

    /** Publish an accepted paper: creates the article in the chosen issue from the submission's details. */
    public function publish(Submission $submission, Issue $issue, ?Admin $by = null): Article
    {
        $this->requireStatus($submission, [SubmissionStatus::Accepted]);

        return DB::transaction(function () use ($submission, $issue, $by) {
            $lang = $submission->language;
            $researcher = $submission->researcher;
            $authors = collect([['name' => $researcher?->name, 'affiliation' => null]])
                ->merge($submission->co_authors ?? [])
                ->filter(fn ($a) => filled($a['name'] ?? null))
                ->map(fn ($a) => ['name' => [$lang => $a['name']], 'affiliation' => [$lang => $a['affiliation'] ?? '']])
                ->values()
                ->all();

            $article = Article::create([
                'issue_id' => $issue->id,
                'type' => ArticleType::FieldResearch,
                'language' => $lang,
                'title' => [$lang => $submission->title],
                'slug' => Str::slug($submission->reference.'-'.Str::limit(Str::slug($submission->title), 60, '')) ?: Str::slug($submission->reference),
                'authors' => $authors,
                'abstract' => [$lang => $submission->abstract],
                'keywords' => $submission->keywords ? [$lang => $submission->keywords] : null,
                'is_published' => false,
                'sort' => $issue->articles()->max('sort') + 1,
            ]);

            if ($latest = $submission->getMedia('manuscript')->last()) {
                $latest->copy($article, 'file');
            }

            $submission->fill(['issue_id' => $issue->id, 'article_id' => $article->id]);
            $this->setStatus($submission, SubmissionStatus::Published, $by);

            return $article;
        });
    }

    public function withdraw(Submission $submission, ?Admin $by = null, ?string $reason = null): void
    {
        if ($submission->status->isClosed()) {
            throw new DomainException(__('This submission is already closed.'));
        }

        $this->setStatus($submission, SubmissionStatus::Withdrawn, $by, $reason);
    }

    /** Admins holding the reviewer role. */
    public function reviewerPool()
    {
        $hasRole = Role::where('name', self::REVIEWER_ROLE)->where('guard_name', 'admin')->exists();

        return $hasRole ? Admin::role(self::REVIEWER_ROLE, 'admin') : Admin::whereRaw('1 = 0');
    }

    private function setStatus(Submission $submission, SubmissionStatus $status, ?Admin $by, ?string $comments = null): void
    {
        $submission->status = $status;
        $submission->save();

        if ($submission->researcher) {
            $submission->researcher->notify(new SubmissionUpdate($submission, $comments));
        }
    }

    private function requireStatus(Submission $submission, array $allowed): void
    {
        if (! in_array($submission->status, $allowed)) {
            throw new DomainException(__('This step is not possible while the submission is ":status".', ['status' => $submission->status->getLabel()]));
        }
    }

    private function log(Submission $submission, string $message): void
    {
        activity('submission')->performedOn($submission)->causedBy(auth('admin')->user())->log($message);
    }

    /** Bell notification to the handling editor, or to everyone who may manage submissions. */
    private function alertEditors(Submission $submission, string $message): void
    {
        try {
            $recipients = $submission->editor ? collect([$submission->editor]) : Admin::permission('Update:Submission')->get();
        } catch (Throwable) {
            $recipients = collect();
        }

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title($submission->reference)
            ->body($message)
            ->actions([Action::make('open')->label(__('Open submission'))->url(SubmissionResource::getUrl('view', ['record' => $submission]))])
            ->sendToDatabase($recipients);
    }
}
