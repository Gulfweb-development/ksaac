<?php

use App\Enums\Site;
use App\Models\Conference;
use App\Models\Issue;
use App\Models\JournalNews;
use App\Models\KidsStory;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Volume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * The old sites are replayed from saved copies in tests/Fixtures/import (real pages, saved Nov 2026).
 * Unknown detail ids answer like the live site does: an empty page. Images and PDFs are tiny stand-ins.
 */
beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    Http::fake(function (Request $request) {
        $url = $request->url();
        $host = str_replace('www.', '', parse_url($url, PHP_URL_HOST));
        $path = ltrim(parse_url($url, PHP_URL_PATH), '/').(($q = parse_url($url, PHP_URL_QUERY)) ? '?'.$q : '');
        $fixture = base_path("tests/Fixtures/import/{$host}/".str_replace(['?', '='], ['@', '-'], $path).'.html');

        return match (true) {
            is_file($fixture) => Http::response(file_get_contents($fixture)),
            (bool) preg_match('/\.(jpe?g|png|gif)$/i', $path) => Http::response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='), 200, ['Content-Type' => 'image/png']),
            str_ends_with(strtolower($path), '.pdf') => Http::response('%PDF-1.4 stand-in', 200, ['Content-Type' => 'application/pdf']),
            str_contains($path, 'Details') => Http::response(file_get_contents(base_path('tests/Fixtures/import/ksaac.org/News_Details.aspx@id-999.html'))),
            default => Http::response('', 404),
        };
    });
});

test('journal import reads every volume and issue from the issue index', function () {
    // The old index lists issue 105 twice (two different issues); the duplicate is reported, not merged.
    $this->artisan('import:live jac --only=issues')
        ->assertSuccessful()
        ->doesntExpectOutputToContain('Could not read the number')
        ->expectsOutputToContain('appears twice');

    $latest = Issue::firstWhere('number', 108);

    expect(Issue::count())->toBe(109)
        ->and(Volume::count())->toBeGreaterThan(20)
        ->and(Issue::whereNull('volume_id')->count())->toBe(0)
        // As listed on the old site: volume 28 is announced but empty, issue 108 sits in volume 27.
        ->and($latest->volume->number)->toBe(27)
        ->and($latest->getTranslation('title', 'ar'))->toBe('العدد المائة وثمانية')
        ->and($latest->is_current)->toBeTrue()
        ->and(Issue::where('is_current', true)->count())->toBe(1)
        ->and($latest->getFirstMedia('cover'))->not->toBeNull()
        ->and($latest->getFirstMedia('file'))->toBeNull();
});

test('issue PDFs download only with --with-pdfs, onto the private disk', function () {
    $this->artisan('import:live jac --only=issues --limit=3 --with-pdfs')->assertSuccessful();

    $pdf = Issue::firstWhere('number', 108)->getFirstMedia('file');

    expect($pdf)->not->toBeNull()->and($pdf->disk)->toBe('local');
});

test('re-running an import does not duplicate records or files', function () {
    $this->artisan('import:live jac --only=issues --limit=5')->assertSuccessful();
    $this->artisan('import:live jac --only=issues --limit=5')->assertSuccessful();

    expect(Issue::count())->toBe(5)
        ->and(Issue::firstWhere('number', 108)->getMedia('cover'))->toHaveCount(1);
});

test('journal news and content pages are imported', function () {
    $this->artisan('import:live jac --only=news,pages')->assertSuccessful();

    expect(JournalNews::where('slug', 'news-8')->first()?->getTranslation('title', 'ar'))->toContain('الطفولة في عصر تكنولوجيا المعلومات')
        ->and(Page::where('site', Site::Jac)->pluck('slug')->sort()->values()->all())->toBe(['about', 'call-for-papers', 'publishing-rules'])
        ->and(Page::where(['site' => Site::Jac, 'slug' => 'publishing-rules'])->first()->getTranslation('content', 'ar'))
        ->toContain('قواعد النشر')->not->toContain('style=')->not->toContain('<span');
});

test('ksaac news keeps Arabic only when the English page repeats the Arabic', function () {
    $this->artisan('import:live ksaac --only=news')->assertSuccessful();

    $news = News::firstWhere('slug', 'news-12');

    expect($news->getTranslation('title', 'ar', false))->toContain('مؤتمر الناشرين')
        ->and($news->getTranslation('title', 'en', false))->toBe('')
        ->and($news->getFirstMedia('image'))->not->toBeNull()
        ->and($news->published_at)->toBeNull()
        ->and($news->getTranslation('body', 'ar'))->not->toContain('style=');
});

test('publications are imported with category, year, cover, and PDFs only on request', function () {
    $this->artisan('import:live ksaac --only=publications')->assertSuccessful();

    $publication = Publication::firstWhere('slug', 'publication-95');

    expect($publication->getTranslation('title', 'ar'))->toBe('دليل لإنشاء مكتبة للأطفال')
        ->and($publication->year)->toBe(1987)
        ->and($publication->category->getTranslation('name', 'ar'))->toBe('مشروع تنمية أدب الأطفال في العالم العربي')
        ->and($publication->getFirstMedia('cover'))->not->toBeNull()
        ->and($publication->getFirstMedia('file'))->toBeNull()
        ->and($publication->getTranslation('description', 'ar'))->not->toContain('سنة النشر');

    $this->artisan('import:live ksaac --only=publications --with-pdfs')->assertSuccessful();

    expect($publication->refresh()->getFirstMedia('file'))->not->toBeNull();
});

test('projects, stories, conferences and about pages are imported', function () {
    $this->artisan('import:live ksaac --only=projects,stories,conferences,pages')->assertSuccessful();

    $story = KidsStory::where('title->ar', 'like', '%دلال%')->first();
    $conference = Conference::firstWhere('slug', 'conference-1');

    expect(Project::firstWhere('slug', 'project-8'))->is_featured->toBeTrue()
        ->and($story->getFirstMedia('cover'))->not->toBeNull()
        ->and($story->getMedia('pages')->count())->toBeGreaterThan(1)
        ->and(Conference::count())->toBe(4)
        ->and($conference->starts_on?->toDateString())->toBe('2018-10-24')
        ->and(Page::where(['site' => Site::Ksaac, 'slug' => 'about-1'])->exists())->toBeTrue();
});

test('document links inside imported pages point at the old site unless PDFs were requested', function () {
    $this->artisan('import:live ksaac --only=pages')->assertSuccessful();
    $content = Page::where(['site' => Site::Ksaac, 'slug' => 'about-1'])->first()->getTranslation('content', 'ar');

    expect($content)->toContain('ksaac.org/images/1050.pdf');

    $this->artisan('import:live ksaac --only=pages --with-pdfs')->assertSuccessful();
    $content = Page::where(['site' => Site::Ksaac, 'slug' => 'about-1'])->first()->getTranslation('content', 'ar');

    expect($content)->toContain('/storage/imported/ksaac.org/1050.pdf');
});

test('an unknown site is refused', function () {
    $this->artisan('import:live example')->assertFailed();
});
