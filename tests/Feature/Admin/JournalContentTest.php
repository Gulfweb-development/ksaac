<?php

use App\Enums\ArticleType;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Filament\Exports;
use App\Filament\Pages\JacSite;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\EditorialMembers\Pages\ListEditorialMembers;
use App\Filament\Resources\Issues\Pages\EditIssue;
use App\Filament\Resources\Issues\RelationManagers\ArticlesRelationManager;
use App\Filament\Resources\SubscriptionPrices\Pages\ListSubscriptionPrices;
use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Filament\Support\TableExports;
use App\Models;
use App\Settings\JacSettings;
use Database\Seeders\JournalContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(superAdmin(), 'admin');
});

function journalIssue(int $volume = 28, int $number = 108): Models\Issue
{
    $vol = Models\Volume::firstOrCreate(['number' => $volume], ['title' => ['ar' => "المجلد $volume", 'en' => "Volume $volume"], 'year' => 2026]);

    return Models\Issue::create(['volume_id' => $vol->id, 'number' => $number, 'title' => ['ar' => "العدد $number", 'en' => "Issue $number"], 'is_published' => true]);
}

function journalArticle(Models\Issue $issue, string $slug, array $extra = []): Models\Article
{
    return Models\Article::create($extra + [
        'issue_id' => $issue->id,
        'type' => ArticleType::FieldResearch,
        'title' => ['ar' => "بحث $slug", 'en' => "Study $slug"],
        'slug' => $slug,
        'authors' => [['name' => ['ar' => 'فاطمة العلي', 'en' => 'Fatima Al-Ali'], 'affiliation' => ['ar' => 'جامعة الكويت', 'en' => 'Kuwait University']]],
    ]);
}

test('every journal screen opens', function (string $uri) {
    $this->get("/admin/$uri")->assertOk();
})->with([
    'website settings' => 'jac/site-settings',
    'volumes' => 'volumes', 'create volume' => 'volumes/create',
    'issues' => 'issues', 'create issue' => 'issues/create',
    'articles' => 'articles', 'create article' => 'articles/create',
    'editorial board' => 'editorial-members', 'create member' => 'editorial-members/create',
    'journal news' => 'journal-news', 'create journal news' => 'journal-news/create',
    'videos' => 'videos', 'create video' => 'videos/create',
    'prices' => 'subscription-prices',
]);

test('the price grid has no create page', function () {
    $this->get('/admin/subscription-prices/create')->assertNotFound();
});

test('the journal seeder copies prices and the editorial board once', function () {
    $this->seed(JournalContentSeeder::class);
    $this->seed(JournalContentSeeder::class);

    expect(Models\SubscriptionPrice::count())->toBe(9)
        ->and(Models\SubscriptionPrice::where(['plan' => SubscriptionPlan::YearlyInstitution, 'region' => SubscriptionRegion::Other])->first())
        ->amount->toBe('60.000')->currency->toBe('USD')
        ->and(Models\EditorialMember::count())->toBe(29)
        ->and(Models\EditorialMember::where('is_deceased', true)->count())->toBe(5);
});

test('prices are edited directly in the table', function () {
    $this->seed(JournalContentSeeder::class);
    $price = Models\SubscriptionPrice::where(['plan' => SubscriptionPlan::SingleIssue, 'region' => SubscriptionRegion::Kuwait])->first();

    Livewire::test(ListSubscriptionPrices::class)
        ->call('updateTableColumnState', 'amount', (string) $price->getKey(), '1.500');

    expect($price->refresh()->amount)->toBe('1.500');
});

test('only one issue is current at a time', function () {
    $old = journalIssue(27, 104);
    $old->update(['is_current' => true]);
    $new = journalIssue(28, 108);
    $new->update(['is_current' => true]);

    expect($old->refresh()->is_current)->toBeFalse()
        ->and($new->refresh()->is_current)->toBeTrue();
});

test('an issue lists its own articles on its edit page', function () {
    $issue = journalIssue();
    $mine = journalArticle($issue, 'mine');
    $other = journalArticle(journalIssue(28, 107), 'other');

    Livewire::test(ArticlesRelationManager::class, ['ownerRecord' => $issue, 'pageClass' => EditIssue::class])
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$other]);
});

test('adding an article from an issue preselects that issue', function () {
    $issue = journalIssue();

    Livewire::withQueryParams(['issue' => $issue->id])
        ->test(CreateArticle::class)
        ->assertSchemaStateSet(['issue_id' => $issue->id]);
});

test('articles save authors in both languages', function () {
    $issue = journalIssue();

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'issue_id' => $issue->id,
            'title' => ['ar' => 'اللعب والتكيف', 'en' => 'Play and Adjustment'],
            'slug' => 'play-and-adjustment',
            'type' => ArticleType::FieldResearch->value,
            'language' => 'ar',
            'authors' => [['name' => ['ar' => 'قاسم الصراف', 'en' => 'Qasem Al-Sarraf'], 'affiliation' => ['ar' => '', 'en' => '']]],
            'page_from' => 11,
            'page_to' => 42,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $article = Models\Article::firstWhere('slug', 'play-and-adjustment');

    expect($article->authorNames('en'))->toBe('Qasem Al-Sarraf')
        ->and($article->authorNames('ar'))->toBe('قاسم الصراف')
        ->and($article->pages())->toBe('11–42');
});

test('last page cannot come before first page', function () {
    Livewire::test(CreateArticle::class)
        ->fillForm(['issue_id' => journalIssue()->id, 'title' => ['ar' => 'ب', 'en' => 'B'], 'slug' => 'b', 'page_from' => 20, 'page_to' => 5])
        ->call('create')
        ->assertHasFormErrors(['page_to']);
});

test('articles filter by volume', function () {
    $in28 = journalArticle(journalIssue(28, 108), 'in-28');
    $in27 = journalArticle(journalIssue(27, 104), 'in-27');

    Livewire::test(ListArticles::class)
        ->filterTable('volume', $in28->issue->volume_id)
        ->assertCanSeeTableRecords([$in28])
        ->assertCanNotSeeTableRecords([$in27]);
});

test('videos need a YouTube link', function () {
    Livewire::test(CreateVideo::class)
        ->fillForm(['title' => ['ar' => 'فيديو', 'en' => 'Video'], 'youtube_url' => 'https://vimeo.com/123'])
        ->call('create')
        ->assertHasFormErrors(['youtube_url']);

    $video = new Models\Video(['youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
    expect($video->thumbnailUrl())->toBe('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
});

test('journal settings save bank accounts', function () {
    Livewire::test(JacSite::class)
        ->fillForm(['bank_accounts' => [['currency' => 'KWD', 'account_number' => '123', 'swift' => 'COMBKWKW', 'iban' => 'KW00']]])
        ->call('save')
        ->assertHasNoErrors();

    expect(app(JacSettings::class)->refresh()->bank_accounts)->toHaveCount(1)
        ->and(setting('jac.bank_accounts.0.account_number'))->toBe('123');
});

test('every journal exporter renders a pdf', function (string $exporter, Closure $make) {
    $make();

    $response = TableExports::pdf($exporter, $exporter::getModel()::all());
    ob_start();
    $response->sendContent();

    expect(ob_get_clean())->toStartWith('%PDF');
})->with([
    'volumes' => [Exports\VolumeExporter::class, fn () => journalIssue()],
    'issues' => [Exports\IssueExporter::class, fn () => journalIssue()],
    'articles' => [Exports\ArticleExporter::class, fn () => journalArticle(journalIssue(), 'a')],
    'board' => [Exports\EditorialMemberExporter::class, fn () => test()->seed(JournalContentSeeder::class)],
    'news' => [Exports\JournalNewsExporter::class, fn () => Models\JournalNews::create(['title' => ['ar' => 'خبر', 'en' => 'News'], 'slug' => 'n', 'published_at' => now()])],
    'videos' => [Exports\VideoExporter::class, fn () => Models\Video::create(['title' => ['ar' => 'فيديو', 'en' => 'Video'], 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ'])],
    'prices' => [Exports\SubscriptionPriceExporter::class, fn () => test()->seed(JournalContentSeeder::class)],
]);

test('a name filled only in Arabic still shows in the English admin', function () {
    app()->setLocale('en');
    $member = Models\EditorialMember::create(['group' => 'trustees', 'name' => ['ar' => 'سعاد الصباح', 'en' => '']]);

    expect($member->name)->toBe('سعاد الصباح');
});

test('editorial board lists trustees, then advisory, then editorial', function () {
    $this->seed(JournalContentSeeder::class);

    $groups = Livewire::test(ListEditorialMembers::class)
        ->instance()->getFilteredSortedTableQuery()->get()->pluck('group')->map->value->unique()->values()->all();

    expect($groups)->toBe(['trustees', 'advisory', 'editorial']);
});

test('english author names use an english comma', function () {
    $article = new Models\Article(['authors' => [['name' => ['en' => 'A']], ['name' => ['en' => 'B']]]]);

    expect($article->authorNames('en'))->toBe('A, B');
});
