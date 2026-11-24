<?php

use App\Enums\Site;
use App\Filament\Exports;
use App\Filament\Pages\Homepage;
use App\Filament\Pages\KsaacSite;
use App\Filament\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Filament\Support\TableExports;
use App\Models;
use App\Settings\HomepageSettings;
use App\Settings\KsaacSettings;
use Database\Seeders\KsaacContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(superAdmin(), 'admin');
});

test('every KSAAC screen opens', function (string $uri) {
    $this->get("/admin/$uri")->assertOk();
})->with([
    'homepage settings' => 'ksaac/homepage',
    'website settings' => 'ksaac/site-settings',
    'menu' => 'menu-items',
    'hero slides' => 'hero-slides',
    'stats' => 'stats',
    'work areas' => 'work-areas',
    'news' => 'news',
    'create news' => 'news/create',
    'publication categories' => 'publication-categories',
    'publications' => 'publications',
    'create publication' => 'publications/create',
    'conferences' => 'conferences',
    'create conference' => 'conferences/create',
    'projects' => 'projects',
    'create project' => 'projects/create',
    'kids stories' => 'kids-stories',
    'partners' => 'partners',
    'board members' => 'board-members',
]);

test('the design seeder fills starter content once', function () {
    $this->seed(KsaacContentSeeder::class);
    $this->seed(KsaacContentSeeder::class);

    expect(Models\MenuItem::where('site', Site::Ksaac)->whereNull('parent_id')->count())->toBe(9)
        ->and(Models\Stat::count())->toBe(5)
        ->and(Models\WorkArea::count())->toBe(4)
        ->and(Models\Partner::count())->toBe(8)
        ->and(Models\Partner::first()->getFirstMedia('logo'))->not->toBeNull();
});

test('menu parent choices stop at two levels and stay within the site', function () {
    $top = Models\MenuItem::create(['site' => Site::Ksaac, 'label' => ['ar' => 'عن', 'en' => 'About']]);
    $column = Models\MenuItem::create(['site' => Site::Ksaac, 'parent_id' => $top->id, 'label' => ['ar' => 'الجمعية', 'en' => 'Society']]);
    $link = Models\MenuItem::create(['site' => Site::Ksaac, 'parent_id' => $column->id, 'label' => ['ar' => 'نبذة', 'en' => 'Who we are'], 'url' => '/about']);
    $jac = Models\MenuItem::create(['site' => Site::Jac, 'label' => ['ar' => 'المجلة', 'en' => 'Journal']]);

    expect($link->depth())->toBe(3);

    Livewire::test(CreateMenuItem::class)
        ->fillForm(['site' => Site::Ksaac->value])
        ->assertFormFieldExists('parent_id', function ($field) use ($top, $column, $link, $jac) {
            $options = array_keys($field->getOptions());

            return in_array($top->id, $options) && in_array($column->id, $options)
                && ! in_array($link->id, $options) && ! in_array($jac->id, $options);
        });
});

test('news slug fills from the english title', function () {
    Livewire::test(CreateNews::class)
        ->fillForm(['title' => ['ar' => 'خبر', 'en' => 'Board Meeting 2026']])
        ->assertSchemaStateSet(['slug' => 'board-meeting-2026']);
});

test('publications save with a category and filter by year', function () {
    $category = Models\PublicationCategory::create(['name' => ['ar' => 'كتب', 'en' => 'Books'], 'slug' => 'books']);

    Livewire::test(CreatePublication::class)
        ->fillForm([
            'title' => ['ar' => 'عدالة الصورة', 'en' => 'Image Justice'],
            'slug' => 'image-justice',
            'publication_category_id' => $category->id,
            'year' => 2019,
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $old = Models\Publication::create(['title' => ['ar' => 'قديم', 'en' => 'Old'], 'slug' => 'old', 'year' => 1999]);
    $new = Models\Publication::firstWhere('slug', 'image-justice');

    expect($new->category->is($category))->toBeTrue();

    Livewire::test(ListPublications::class)
        ->filterTable('year', 2019)
        ->assertCanSeeTableRecords([$new])
        ->assertCanNotSeeTableRecords([$old]);
});

test('homepage settings save section visibility and order', function () {
    $component = Livewire::test(Homepage::class);
    $sections = $component->get('data.sections');
    $keys = array_keys($sections);

    // Hide the first section and move the last one to the top.
    $sections[$keys[0]]['visible'] = false;
    $last = array_pop($sections);
    $sections = [array_key_last($keys) => $last] + $sections;

    $component->set('data.sections', $sections)->call('save')->assertHasNoErrors();

    $saved = app(HomepageSettings::class)->refresh()->sections;

    expect($saved[0]['key'])->toBe('newsletter')
        ->and(collect($saved)->firstWhere('key', 'hero')['visible'])->toBeFalse()
        ->and($saved)->toHaveCount(count(HomepageSettings::SECTIONS));
});

test('homepage settings restore a section missing from saved data', function () {
    $settings = app(HomepageSettings::class);
    $settings->sections = array_values(array_filter($settings->sections, fn ($s) => $s['key'] !== 'kids'));
    $settings->save();

    $keys = collect(Livewire::test(Homepage::class)->get('data.sections'))->pluck('key');

    expect($keys)->toContain('kids')->toHaveCount(count(HomepageSettings::SECTIONS));
});

test('website settings save bilingual values', function () {
    Livewire::test(KsaacSite::class)
        ->fillForm(['phone' => '+965 2474 8387', 'address' => ['ar' => 'الكويت', 'en' => 'Kuwait']])
        ->call('save')
        ->assertHasNoErrors();

    expect(app(KsaacSettings::class)->refresh()->address)->toEqual(['ar' => 'الكويت', 'en' => 'Kuwait'])
        ->and(setting('ksaac.phone'))->toBe('+965 2474 8387');
});

test('every KSAAC exporter renders a pdf', function (string $exporter, Closure $make) {
    $make();

    $response = TableExports::pdf($exporter, $exporter::getModel()::all());
    ob_start();
    $response->sendContent();

    expect(ob_get_clean())->toStartWith('%PDF');
})->with([
    'menu' => [Exports\MenuItemExporter::class, fn () => Models\MenuItem::create(['site' => Site::Ksaac, 'label' => ['ar' => 'الرئيسية', 'en' => 'Home'], 'url' => '/'])],
    'hero slides' => [Exports\HeroSlideExporter::class, fn () => Models\HeroSlide::create(['title' => ['ar' => 'عنوان', 'en' => 'Title']])],
    'stats' => [Exports\StatExporter::class, fn () => Models\Stat::create(['value' => 46, 'label' => ['ar' => 'عاماً', 'en' => 'Years']])],
    'work areas' => [Exports\WorkAreaExporter::class, fn () => Models\WorkArea::create(['title' => ['ar' => 'بحوث', 'en' => 'Research']])],
    'news' => [Exports\NewsExporter::class, fn () => Models\News::create(['title' => ['ar' => 'خبر', 'en' => 'News'], 'slug' => 'n', 'published_at' => now()])],
    'categories' => [Exports\PublicationCategoryExporter::class, fn () => Models\PublicationCategory::create(['name' => ['ar' => 'كتب', 'en' => 'Books'], 'slug' => 'b'])],
    'publications' => [Exports\PublicationExporter::class, fn () => Models\Publication::create(['title' => ['ar' => 'كتاب', 'en' => 'Book'], 'slug' => 'p', 'year' => 2020])],
    'conferences' => [Exports\ConferenceExporter::class, fn () => Models\Conference::create(['title' => ['ar' => 'مؤتمر', 'en' => 'Conference'], 'slug' => 'c', 'starts_on' => now()])],
    'projects' => [Exports\ProjectExporter::class, fn () => Models\Project::create(['title' => ['ar' => 'مشروع', 'en' => 'Project'], 'slug' => 'pr'])],
    'kids stories' => [Exports\KidsStoryExporter::class, fn () => Models\KidsStory::create(['title' => ['ar' => 'قصة', 'en' => 'Story']])],
    'partners' => [Exports\PartnerExporter::class, fn () => Models\Partner::create(['name' => ['ar' => 'شريك', 'en' => 'Partner']])],
    'board' => [Exports\BoardMemberExporter::class, fn () => Models\BoardMember::create(['name' => ['ar' => 'عضو', 'en' => 'Member']])],
]);

test('former pop-up forms now open as their own create page', function (string $resource) {
    $this->get("/admin/$resource/create")->assertOk();
})->with(['menu-items', 'hero-slides', 'stats', 'work-areas', 'publication-categories', 'kids-stories', 'partners', 'board-members', 'social-links', 'newsletter-subscribers']);
