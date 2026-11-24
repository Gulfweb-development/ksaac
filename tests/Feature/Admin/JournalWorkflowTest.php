<?php

use App\Enums\ReviewRecommendation;
use App\Enums\ReviewStatus;
use App\Enums\SubmissionStatus;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Enums\SubscriptionStatus;
use App\Filament\Exports\SubmissionExporter;
use App\Filament\Exports\SubmissionReviewExporter;
use App\Filament\Exports\SubscriptionExporter;
use App\Filament\Resources\MyReviews\Pages\EditMyReview;
use App\Filament\Resources\MyReviews\Pages\ListMyReviews;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Support\TableExports;
use App\Models\Admin;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Volume;
use App\Notifications\ReviewInvitation;
use App\Notifications\SubmissionUpdate;
use App\Notifications\SubscriptionDecision;
use App\Services\Journal\PeerReview;
use App\Services\Journal\SubscriptionDesk;
use Database\Seeders\EditorRolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Notification::fake();
    Storage::fake('local');
    $this->editor = superAdmin();
    $this->seed(EditorRolesSeeder::class);
    $this->actingAs($this->editor, 'admin');
});

function reviewer(string $name = 'Reviewer'): Admin
{
    return tap(Admin::factory()->create(['name' => $name, 'email' => fake()->unique()->safeEmail()]))->assignRole(PeerReview::REVIEWER_ROLE);
}

function paper(array $attributes = []): Submission
{
    $researcher = User::factory()->create(['name' => 'Dr. Hidden Author', 'email' => 'hidden.author@example.com']);
    $submission = Submission::create($attributes + [
        'user_id' => $researcher->id,
        'title' => 'Play and adjustment in kindergarten',
        'abstract' => 'An abstract.',
        'co_authors' => [['name' => 'Co Author', 'affiliation' => 'Kuwait University']],
    ]);
    $submission->addMedia(UploadedFile::fake()->create('named.pdf', 20, 'application/pdf'))->toMediaCollection('manuscript');
    $submission->addMedia(UploadedFile::fake()->create('blind.pdf', 20, 'application/pdf'))->toMediaCollection('blind_manuscript');

    return $submission->refresh();
}

function review(Submission $submission, Admin $reviewer, ReviewRecommendation $recommendation)
{
    $review = $submission->reviews()->where('reviewer_id', $reviewer->id)->where('round', $submission->round)->firstOrFail();

    return app(PeerReview::class)->complete($review, [
        'scores' => ['originality' => 4, 'methodology' => 3, 'clarity' => 4, 'contribution' => 5],
        'recommendation' => $recommendation,
        'comments_to_author' => "Comments from {$reviewer->name}",
    ]);
}

function issue108(): Issue
{
    $volume = Volume::create(['number' => 28, 'title' => ['ar' => 'المجلد 28', 'en' => 'Volume 28']]);

    return Issue::create(['volume_id' => $volume->id, 'number' => 108, 'title' => ['ar' => 'العدد 108', 'en' => 'Issue 108']]);
}

function subscriptionRequest(SubscriptionPlan $plan = SubscriptionPlan::YearlyIndividual): Subscription
{
    return Subscription::create([
        'name' => 'Reader One', 'email' => 'reader@example.com', 'phone' => '99999999',
        'plan' => $plan, 'region' => SubscriptionRegion::Kuwait, 'amount' => 3, 'currency' => 'KWD',
    ]);
}

// --- Subscriptions ---------------------------------------------------------------------------------

test('approving a yearly subscription creates the reader account and a one-year access period', function () {
    $subscription = subscriptionRequest();

    $user = app(SubscriptionDesk::class)->approve($subscription, now()->startOfDay(), by: $this->editor);

    expect($subscription->refresh())
        ->status->toBe(SubscriptionStatus::Approved)
        ->ends_on->toDateString()->toBe(now()->addYear()->subDay()->toDateString())
        ->and($user->email)->toBe('reader@example.com')
        ->and($user->canReadIssue(issue108()))->toBeTrue();

    Notification::assertSentTo($user, SubscriptionDecision::class, fn ($n) => $n->newAccount);
});

test('a single-issue purchase needs its issue and unlocks only that issue', function () {
    $issue = issue108();
    $other = Issue::create(['volume_id' => $issue->volume_id, 'number' => 107, 'title' => ['ar' => '107', 'en' => '107']]);
    $subscription = subscriptionRequest(SubscriptionPlan::SingleIssue);

    expect(fn () => app(SubscriptionDesk::class)->approve($subscription, today()))->toThrow(DomainException::class);

    $user = app(SubscriptionDesk::class)->approve($subscription, today(), $issue->id);

    expect($user->canReadIssue($issue))->toBeTrue()
        ->and($user->canReadIssue($other))->toBeFalse()
        ->and($subscription->refresh()->ends_on)->toBeNull();
});

test('expired subscriptions no longer unlock issues', function () {
    $user = app(SubscriptionDesk::class)->approve(subscriptionRequest(), now()->subYears(2));

    expect($user->canReadIssue(issue108()))->toBeFalse();
});

test('rejecting a request emails the reason and it cannot be handled twice', function () {
    $subscription = subscriptionRequest();

    app(SubscriptionDesk::class)->reject($subscription, 'Receipt is unreadable.');

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::Rejected)
        ->and(User::count())->toBe(0);
    Notification::assertSentTo(new AnonymousNotifiable, SubscriptionDecision::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'reader@example.com');
    expect(fn () => app(SubscriptionDesk::class)->approve($subscription, today()))->toThrow(DomainException::class);
});

test('editors approve a request from the subscriptions table', function () {
    $subscription = subscriptionRequest();

    Livewire::test(ListSubscriptions::class)
        ->callTableAction('approve', $subscription, ['starts_on' => today()->toDateString()])
        ->assertHasNoTableActionErrors();

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::Approved);
});

// --- Peer review -----------------------------------------------------------------------------------

test('reviewers cannot be invited before an anonymised manuscript exists', function () {
    $submission = Submission::create(['user_id' => User::factory()->create()->id, 'title' => 'T', 'abstract' => 'A']);

    expect(fn () => app(PeerReview::class)->assignReviewers($submission, [reviewer()->id]))->toThrow(DomainException::class);
});

test('sending to two reviewers invites them and tells the researcher', function () {
    $submission = paper();
    [$a, $b] = [reviewer('A'), reviewer('B')];

    app(PeerReview::class)->assignReviewers($submission, [$a->id, $b->id], by: $this->editor);

    expect($submission->refresh()->status)->toBe(SubmissionStatus::UnderReview)
        ->and($submission->reviews()->count())->toBe(2);
    Notification::assertSentTo([$a, $b], ReviewInvitation::class);
    Notification::assertSentTo($submission->researcher, SubmissionUpdate::class);
});

test('a third reviewer is invited automatically when the first two disagree', function () {
    $submission = paper();
    [$a, $b, $c] = [reviewer('A'), reviewer('B'), reviewer('C')];
    app(PeerReview::class)->assignReviewers($submission, [$a->id, $b->id]);

    expect(review($submission, $a, ReviewRecommendation::Accept))->toBeNull();
    $third = review($submission, $b, ReviewRecommendation::Reject);

    expect($third)->not->toBeNull()
        ->and($third->reviewer->is($c))->toBeTrue()
        ->and($submission->reviews()->count())->toBe(3);
    Notification::assertSentTo($c, ReviewInvitation::class);
});

test('no third reviewer when the first two agree', function () {
    $submission = paper();
    [$a, $b] = [reviewer('A'), reviewer('B')];
    reviewer('C');
    app(PeerReview::class)->assignReviewers($submission, [$a->id, $b->id]);

    review($submission, $a, ReviewRecommendation::Accept);
    expect(review($submission, $b, ReviewRecommendation::MinorRevisions))->toBeNull()
        ->and($submission->reviews()->count())->toBe(2);
});

test('accepting sets the 150 KWD reward and emails the researcher the comments', function () {
    $submission = paper();
    $a = reviewer('A');
    app(PeerReview::class)->assignReviewers($submission, [$a->id]);
    review($submission, $a, ReviewRecommendation::Accept);

    app(PeerReview::class)->decide($submission->refresh(), SubmissionStatus::Accepted, 'Well done.', $this->editor);

    expect($submission->refresh())->status->toBe(SubmissionStatus::Accepted)->reward_amount->toBe('150.000');
    Notification::assertSentTo($submission->researcher, SubmissionUpdate::class, fn ($n) => $n->comments === 'Well done.');
});

test('a revision round re-invites the reviewers who completed the last round', function () {
    $submission = paper();
    [$a, $b] = [reviewer('A'), reviewer('B')];
    app(PeerReview::class)->assignReviewers($submission, [$a->id, $b->id]);
    review($submission, $a, ReviewRecommendation::MajorRevisions);
    review($submission, $b, ReviewRecommendation::MajorRevisions);
    app(PeerReview::class)->decide($submission->refresh(), SubmissionStatus::RevisionsRequested);

    app(PeerReview::class)->startRevisionRound($submission->refresh());

    expect($submission->refresh())->round->toBe(2)->status->toBe(SubmissionStatus::UnderReview)
        ->and($submission->reviews()->where('round', 2)->pluck('reviewer_id')->sort()->values()->all())->toBe(collect([$a->id, $b->id])->sort()->values()->all());
});

test('publishing creates an unpublished article with authors and the newest manuscript', function () {
    $submission = paper(['status' => SubmissionStatus::Accepted]);
    $issue = issue108();

    $article = app(PeerReview::class)->publish($submission, $issue, $this->editor);

    expect($submission->refresh())->status->toBe(SubmissionStatus::Published)->article_id->toBe($article->id)
        ->and($article->issue_id)->toBe($issue->id)
        ->and($article->is_published)->toBeFalse()
        ->and($article->authorNames('ar'))->toBe('Dr. Hidden Author، Co Author')
        ->and($article->getFirstMedia('file'))->not->toBeNull()
        ->and($submission->activities()->count())->toBeGreaterThan(0);
});

test('invalid steps are refused', function () {
    $submission = paper();

    expect(fn () => app(PeerReview::class)->publish($submission, issue108()))->toThrow(DomainException::class)
        ->and(fn () => app(PeerReview::class)->decide($submission, SubmissionStatus::Published))->toThrow(DomainException::class);
});

// --- Screens and access ----------------------------------------------------------------------------

test('editors open every workflow screen', function () {
    $submission = paper();
    app(PeerReview::class)->assignReviewers($submission, [reviewer()->id]);

    foreach (['/admin/subscriptions', '/admin/subscriptions/create', '/admin/subscriptions/'.subscriptionRequest()->id.'/edit',
        '/admin/submissions', '/admin/submissions/create', "/admin/submissions/{$submission->id}", "/admin/submissions/{$submission->id}/edit"] as $uri) {
        $this->get($uri)->assertOk();
    }
});

test('reviewers see only their own reviews, never the researcher, and cannot reach submissions', function () {
    $submission = paper();
    [$mine, $theirs] = [reviewer('Me'), reviewer('Someone else')];
    app(PeerReview::class)->assignReviewers($submission, [$mine->id, $theirs->id]);
    $myReview = $submission->reviews()->where('reviewer_id', $mine->id)->first();
    $theirReview = $submission->reviews()->where('reviewer_id', $theirs->id)->first();

    $this->actingAs($mine, 'admin');

    Livewire::test(ListMyReviews::class)->assertCanSeeTableRecords([$myReview])->assertCanNotSeeTableRecords([$theirReview]);

    $this->get("/admin/my-reviews/{$myReview->id}")
        ->assertOk()
        ->assertSee('Play and adjustment in kindergarten')
        ->assertDontSee('Dr. Hidden Author')
        ->assertDontSee('hidden.author@example.com')
        ->assertDontSee('Co Author')
        ->assertDontSee('named.pdf');
    $this->get("/admin/my-reviews/{$theirReview->id}")->assertNotFound();
    $this->get('/admin/submissions')->assertForbidden();
    $this->get('/admin/subscriptions')->assertForbidden();
});

test('a reviewer accepts and submits a review from their review page', function () {
    $submission = paper();
    $me = reviewer('Me');
    app(PeerReview::class)->assignReviewers($submission, [$me->id]);
    $review = $submission->reviews()->first();
    $this->actingAs($me, 'admin');

    Livewire::test(EditMyReview::class, ['record' => $review->getRouteKey()])
        ->callAction('accept')
        ->fillForm([
            'scores' => ['originality' => 5, 'methodology' => 4, 'clarity' => 4, 'contribution' => 3],
            'recommendation' => ReviewRecommendation::MinorRevisions->value,
            'comments_to_author' => 'Clarify the sample.',
            'comments_to_editor' => 'Solid work.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($review->refresh())->status->toBe(ReviewStatus::Completed)->averageScore()->toBe(4.0);
});

test('editor roles stay within their own website', function () {
    $ksaac = tap(Admin::factory()->create(['email' => 'ksaac.editor@example.com']))->assignRole('ksaac_editor');
    $journal = tap(Admin::factory()->create(['email' => 'journal.editor@example.com']))->assignRole('journal_editor');

    $this->actingAs($ksaac, 'admin')->get('/admin/news')->assertOk();
    $this->actingAs($ksaac, 'admin')->get('/admin/submissions')->assertForbidden();
    $this->actingAs($ksaac, 'admin')->get('/admin/jac/site-settings')->assertForbidden();
    // New session per user: the session-auth middleware logs out when the signed-in user changes.
    $this->flushSession();
    $this->actingAs($journal, 'admin')->get('/admin/submissions')->assertOk();
    $this->actingAs($journal, 'admin')->get('/admin/news')->assertForbidden();
    $this->actingAs($journal, 'admin')->get('/admin/ksaac/homepage')->assertForbidden();
});

test('workflow tables export to pdf', function (string $exporter) {
    app(PeerReview::class)->assignReviewers(paper(), [reviewer()->id]);
    subscriptionRequest();

    $response = TableExports::pdf($exporter, $exporter::getModel()::all());
    ob_start();
    $response->sendContent();

    expect(ob_get_clean())->toStartWith('%PDF');
})->with([SubmissionExporter::class, SubscriptionExporter::class, SubmissionReviewExporter::class]);
