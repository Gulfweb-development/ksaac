<?php

use App\Enums\Site;
use App\Filament\Exports\PageExporter;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\NewsletterSubscribers\Pages\CreateNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\SocialLinks\Pages\CreateSocialLink;
use App\Filament\Support\TableExports;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\SocialLink;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(superAdmin(), 'admin');
});

test('shared content screens open', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    'pages' => '/admin/pages',
    'create page' => '/admin/pages/create',
    'social links' => '/admin/social-links',
    'newsletter subscribers' => '/admin/newsletter-subscribers',
    'contact messages' => '/admin/contact-messages',
]);

test('the same email can subscribe once per site', function () {
    NewsletterSubscriber::factory()->create(['site' => Site::Ksaac, 'email' => 'reader@example.com']);

    Livewire::test(CreateNewsletterSubscriber::class)
        ->fillForm(['site' => Site::Ksaac->value, 'email' => 'reader@example.com', 'locale' => 'ar'])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);

    Livewire::test(CreateNewsletterSubscriber::class)
        ->fillForm(['site' => Site::Jac->value, 'email' => 'reader@example.com', 'locale' => 'ar'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(NewsletterSubscriber::where('email', 'reader@example.com')->count())->toBe(2);
});

test('subscriber status filter separates unsubscribed people', function () {
    $active = NewsletterSubscriber::factory()->create();
    $gone = NewsletterSubscriber::factory()->create(['unsubscribed_at' => now()]);

    Livewire::test(ListNewsletterSubscribers::class)
        ->filterTable('subscribed', false)
        ->assertCanSeeTableRecords([$gone])
        ->assertCanNotSeeTableRecords([$active]);
});

test('opening a contact message marks it read and updates the unread badge', function () {
    $message = ContactMessage::factory()->create();
    ContactMessage::factory()->create();

    expect(ContactMessageResource::getNavigationBadge())->toBe('2');

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])->assertOk();

    expect($message->refresh()->read_at)->not->toBeNull()
        ->and(ContactMessageResource::getNavigationBadge())->toBe('1');
});

test('contact messages cannot be created from the admin', function () {
    $this->get('/admin/contact-messages/create')->assertNotFound();
    expect(ContactMessageResource::canCreate())->toBeFalse();
});

test('contact messages filter by site', function () {
    $ksaac = ContactMessage::factory()->create(['site' => Site::Ksaac]);
    $jac = ContactMessage::factory()->create(['site' => Site::Jac]);

    Livewire::test(ListContactMessages::class)
        ->filterTable('site', Site::Jac->value)
        ->assertCanSeeTableRecords([$jac])
        ->assertCanNotSeeTableRecords([$ksaac]);
});

test('social links can be added', function () {
    Livewire::test(CreateSocialLink::class)
        ->fillForm(['site' => Site::Ksaac->value, 'platform' => 'instagram', 'url' => 'https://instagram.com/ksaac', 'is_active' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SocialLink::first())->platform->value->toBe('instagram');
});

test('a page saves arabic and english and refills both on edit', function () {
    Livewire::test(CreatePage::class)
        ->fillForm([
            'site' => Site::Ksaac->value,
            'title' => ['ar' => 'عن الجمعية', 'en' => 'About the Society'],
            'slug' => 'about',
            'content' => ['ar' => '<p>نبذة</p>', 'en' => '<p>Who we are</p>'],
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::firstWhere('slug', 'about');

    expect($page->getTranslation('title', 'ar'))->toBe('عن الجمعية')
        ->and($page->getTranslation('title', 'en'))->toBe('About the Society');

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertSchemaStateSet(['title.ar' => 'عن الجمعية', 'title.en' => 'About the Society']);
});

test('page slugs are unique per site only', function () {
    Page::factory()->create(['site' => Site::Ksaac, 'slug' => 'about']);

    Livewire::test(CreatePage::class)
        ->fillForm(['site' => Site::Ksaac->value, 'title' => ['ar' => 'عن', 'en' => 'About'], 'slug' => 'about'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);

    Livewire::test(CreatePage::class)
        ->fillForm(['site' => Site::Jac->value, 'title' => ['ar' => 'عن المجلة', 'en' => 'About'], 'slug' => 'about'])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('pages table searches titles', function () {
    $about = Page::factory()->create(['title' => ['ar' => 'عن', 'en' => 'About us']]);
    $rules = Page::factory()->create(['title' => ['ar' => 'قواعد', 'en' => 'Publishing rules']]);

    Livewire::test(ListPages::class)
        ->searchTable('Publishing')
        ->assertCanSeeTableRecords([$rules])
        ->assertCanNotSeeTableRecords([$about]);
});

test('pages export to pdf with both title languages', function () {
    Page::factory()->create();

    $response = TableExports::pdf(PageExporter::class, Page::all());

    ob_start();
    $response->sendContent();

    expect(ob_get_clean())->toStartWith('%PDF');
});
