<?php

namespace App\Services\Import;

use App\Enums\Site;
use App\Models\Conference;
use App\Models\KidsStory;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Publication;
use App\Models\PublicationCategory;
use App\Support\Import\HtmlPage;
use DOMElement;
use Illuminate\Support\Carbon;

/**
 * Imports ksaac.org: news, publications (by category, with cover and PDF), projects, kids' stories,
 * conferences and the About pages. Detail pages share one layout: a <section> holding an h2.section-title,
 * an image and Word-pasted HTML. The old pages carry no news dates, so publish dates stay empty for editors to fill.
 */
class KsaacImporter extends Importer
{
    private const MONTHS = [
        'يناير' => 1, 'فبراير' => 2, 'مارس' => 3, 'أبريل' => 4, 'ابريل' => 4, 'مايو' => 5, 'يونيو' => 6, 'يوليو' => 7,
        'أغسطس' => 8, 'اغسطس' => 8, 'سبتمبر' => 9, 'أكتوبر' => 10, 'اكتوبر' => 10, 'نوفمبر' => 11, 'ديسمبر' => 12,
    ];

    protected function base(): string
    {
        return 'https://ksaac.org';
    }

    public function steps(): array
    {
        return [
            'news' => fn () => $this->news(),
            'publications' => fn () => $this->publications(),
            'projects' => fn () => $this->projects(),
            'stories' => fn () => $this->stories(),
            'conferences' => fn () => $this->conferences(),
            'pages' => fn () => $this->pages(),
        ];
    }

    private function news(): void
    {
        // No list page on the old site.
        $this->probe('Default.aspx', 'News_Details.aspx', 'news', function (int $id) {
            $ar = $this->detail("News_Details.aspx?id={$id}");

            if (! $ar) {
                return false;
            }

            $en = $this->detail("News_Details_en.aspx?id={$id}");

            $news = News::updateOrCreate(['slug' => "news-{$id}"], [
                'title' => self::both($ar['title'], $en['title'] ?? null),
                'body' => self::both($ar['body'], $en['body'] ?? null),
                'is_published' => true,
            ]);
            $this->attach($news, 'image', $ar['images'][0] ?? null);
            $this->count('news');

            return true;
        });
    }

    private function publications(): void
    {
        foreach ($this->listIds('Publications.aspx', 'Publications_Cat.aspx') as $cat) {
            $page = $this->page("Publications_Cat.aspx?id={$cat}");
            $name = $page?->text('//h2['.HtmlPage::hasClass('section-title').']');

            if (! $name) {
                continue;
            }

            $category = PublicationCategory::updateOrCreate(['slug' => "category-{$cat}"], ['name' => ['ar' => $name], 'sort' => $cat]);
            $this->count('publication categories');

            $ids = collect($page->all("//a[contains(@href, 'Publication_Details.aspx')]"))
                ->map(fn (DOMElement $a) => (int) preg_replace('/\D/', '', strstr($a->getAttribute('href'), 'id=')))
                ->unique()
                ->filter();

            foreach ($ids as $id) {
                if ($this->reachedLimit('publications')) {
                    return;
                }

                $this->publication($id, $category);
            }
        }
    }

    private function publication(int $id, PublicationCategory $category): void
    {
        $page = $this->page("Publication_Details.aspx?id={$id}");
        $panel = $page?->first("//*[@id='ContentPlaceHolder1_pnlFile']");
        $title = $panel ? $page->text('.//h2', $panel) : null;

        if (! $title) {
            return;
        }

        $text = (string) HtmlPage::clean($panel->textContent);
        $author = preg_match('/إسم المؤلف\s*:\s*(.*?)\s*(?:عدد الصفحات|سنة النشر|$)/u', $text, $m) ? trim($m[1], ' :-') : null;
        $year = preg_match('/سنة النشر\s*:\s*(\d{4})/u', $text, $m) ? (int) $m[1] : null;

        $cover = $page->first(".//img[contains(@src, 'images/') and not(contains(@src, 'pdf.jpg'))]", $panel);
        $pdf = $page->first(".//a[contains(@href, '.pdf')]", $panel);

        // Description = the panel minus title, images, the PDF link and the "author / pages / year" field lines.
        foreach ($page->all('.//h2 | .//img | .//a[contains(@href, ".pdf")]', $panel) as $node) {
            $node->parentNode?->removeChild($node);
        }
        foreach ($page->all('.//*[not(*)]', $panel) as $leaf) {
            if (preg_match('/إسم المؤلف|عدد الصفحات|سنة النشر|السعر/u', $leaf->textContent)) {
                $leaf->parentNode?->removeChild($leaf);
            }
        }

        $en = $this->page("Publication_Details_en.aspx?id={$id}");
        $enTitle = $en?->text("//*[@id='ContentPlaceHolder1_pnlFile']//h2");

        $publication = Publication::updateOrCreate(['slug' => "publication-{$id}"], [
            'publication_category_id' => $category->id,
            'title' => self::both($title, $enTitle),
            'author' => $author ? ['ar' => $author] : null,
            'year' => $year,
            'description' => self::both($this->rich(HtmlPage::innerHtml($panel), $page->url)),
            'is_published' => true,
        ]);

        $this->attach($publication, 'cover', $page->absolute($cover?->getAttribute('src')));

        if ($this->withPdfs) {
            $this->attach($publication, 'file', $page->absolute($pdf?->getAttribute('href')));
        }

        $this->count('publications');
    }

    private function projects(): void
    {
        foreach ($this->listIds('Projects.aspx', 'Projects_Details.aspx') as $id) {
            if ($this->reachedLimit('projects') || ! ($ar = $this->detail("Projects_Details.aspx?id={$id}"))) {
                continue;
            }

            $en = $this->detail("Projects_Details_en.aspx?id={$id}");
            $project = Project::updateOrCreate(['slug' => "project-{$id}"], [
                'title' => self::both($ar['title'], $en['title'] ?? null),
                'body' => self::both($ar['body'], $en['body'] ?? null),
                'is_published' => true,
            ]);
            $this->attach($project, 'image', $ar['images'][0] ?? null);
            $this->count('projects');
        }

        // The newest project (highest old id) fills the homepage project block, unless editors already chose one.
        if (Project::where('is_featured', true)->doesntExist()) {
            Project::where('slug', 'like', 'project-%')->get()
                ->sortByDesc(fn (Project $project) => (int) str_replace('project-', '', $project->slug))
                ->first()
                ?->update(['is_featured' => true]);
        }
    }

    private function stories(): void
    {
        foreach ($this->listIds('Stories.aspx', 'Story_Details.aspx') as $i => $id) {
            if ($this->reachedLimit('stories') || ! ($ar = $this->detail("Story_Details.aspx?id={$id}"))) {
                continue;
            }

            $story = KidsStory::where('title->ar', $ar['title'])->first() ?? new KidsStory;
            $story->fill(['title' => ['ar' => $ar['title']], 'is_published' => true, 'sort' => $i + 1])->save();

            $this->attach($story, 'cover', $ar['images'][0] ?? null);
            foreach ($ar['images'] as $image) {
                $this->attach($story, 'pages', $image);
            }

            $this->count('stories');
        }
    }

    private function conferences(): void
    {
        $page = $this->page('Activities.aspx');

        foreach ($page?->all('//div['.HtmlPage::hasClass('panel-group').']/div['.HtmlPage::hasClass('panel').']') ?? [] as $i => $panel) {
            $title = $page->text('.//*['.HtmlPage::hasClass('panel-title').']', $panel);
            $body = $page->first('.//*['.HtmlPage::hasClass('panel-body').']', $panel);

            if (! $title || ! $body) {
                continue;
            }

            $images = array_values(array_filter(array_map(fn ($img) => $page->absolute($img->getAttribute('src')), $page->all('.//img', $body))));
            foreach ($page->all('.//img', $body) as $img) {
                $img->parentNode->removeChild($img);
            }

            $conference = Conference::updateOrCreate(['slug' => 'conference-'.($i + 1)], [
                'title' => ['ar' => $title],
                'starts_on' => self::arabicDate($title.' '.$body->textContent),
                'body' => ['ar' => $this->rich(HtmlPage::innerHtml($body), $page->url)],
                'is_published' => true,
            ]);

            $this->attach($conference, 'image', $images[0] ?? null);
            foreach (array_slice($images, 1) as $image) {
                $this->attach($conference, 'gallery', $image);
            }

            $this->count('conferences');
        }
    }

    private function pages(): void
    {
        foreach ($this->listIds('about.aspx', 'AboutDetails.aspx') as $id) {
            if (! ($ar = $this->detail("AboutDetails.aspx?id={$id}", keepImages: true))) {
                continue;
            }

            Page::updateOrCreate(['site' => Site::Ksaac, 'slug' => "about-{$id}"], [
                'title' => ['ar' => $ar['title']],
                'content' => ['ar' => $ar['body']],
                'is_published' => true,
            ]);
            $this->count('pages');
        }
    }

    /**
     * Title, cleaned body and image URLs from a standard detail page; null when the page is empty.
     *
     * @return array{title: string, body: ?string, images: list<string>}|null
     */
    private function detail(string $path, bool $keepImages = false): ?array
    {
        $page = $this->page($path);
        $heading = $page?->first('//h2['.HtmlPage::hasClass('section-title').']');
        $title = $heading ? HtmlPage::clean($heading->textContent) : null;

        if (! $title) {
            return null;
        }

        $section = $page->first('ancestor::section[1]', $heading) ?? $heading->parentNode;
        $images = [];

        foreach ($page->all('.//img', $section) as $img) {
            $src = $page->absolute($img->getAttribute('src'));

            if ($src && ! preg_match('~pdf\.jpg|logo|icon~i', $src)) {
                $images[] = $src;
            }

            if (! $keepImages) {
                $img->parentNode->removeChild($img);
            }
        }

        $heading->parentNode->removeChild($heading);

        return ['title' => $title, 'body' => $this->rich(HtmlPage::innerHtml($section), $page->url), 'images' => array_values(array_unique($images))];
    }

    /** @return list<int> detail ids linked from a list page, in page order */
    private function listIds(string $listPath, string $detailPage): array
    {
        $page = $this->page($listPath);

        return collect($page?->all("//a[contains(@href, '{$detailPage}')]") ?? [])
            ->map(fn (DOMElement $a) => (int) preg_replace('/\D/', '', (string) strstr($a->getAttribute('href'), 'id=')))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** First "24 أكتوبر 2018"-style date in the text. */
    private static function arabicDate(string $text): ?Carbon
    {
        $months = implode('|', array_keys(self::MONTHS));

        if (preg_match('/(\d{1,2})\s*('.$months.')\s*(\d{4})/u', $text, $m)) {
            return Carbon::create((int) $m[3], self::MONTHS[$m[2]], (int) $m[1]);
        }

        return null;
    }
}
