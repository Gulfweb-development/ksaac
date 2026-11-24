<?php

namespace App\Services\Import;

use App\Enums\Site;
use App\Models\Issue;
use App\Models\JournalNews;
use App\Models\Page;
use App\Models\Volume;
use App\Support\Import\ArabicNumber;
use App\Support\Import\HtmlPage;

/**
 * Imports jac-kw.org: volumes and issues (cover, optional full PDF), news, and the content pages.
 * The old site has no per-issue article lists, so articles are not imported.
 */
class JacImporter extends Importer
{
    protected function base(): string
    {
        return 'https://www.jac-kw.org';
    }

    public function steps(): array
    {
        return [
            'issues' => fn () => $this->issues(),
            'news' => fn () => $this->news(),
            'pages' => fn () => $this->pages(),
        ];
    }

    private function issues(): void
    {
        $page = $this->page('Magazin.aspx');

        if (! $page) {
            return;
        }

        $volume = null;
        $seen = []; // issue number => old site id, to catch the same number listed twice
        $nodes = $page->all("//*[contains(@id, 'Magazins_Cat_Name_ARLabel')] | //span[contains(@id, 'Magazins_Details_Name_ARLabel')]");

        // Document order: each volume label is followed by the labels of its issues.
        foreach ($nodes as $node) {
            $label = HtmlPage::clean($node->textContent);
            $number = ArabicNumber::parse((string) $label);

            if ($number === null) {
                $this->client->problems[] = "Could not read the number in \"{$label}\"";

                continue;
            }

            if (str_contains($node->getAttribute('id'), 'Magazins_Cat')) {
                $volume = Volume::updateOrCreate(['number' => $number], ['title' => ['ar' => $label, 'en' => "Volume {$number}"]]);
                $this->count('volumes');

                continue;
            }

            if (! $volume || $this->reachedLimit('issues')) {
                continue;
            }

            $block = $page->first('ancestor::table[1]', $node);
            $oldId = preg_replace('/\D/', '', (string) strstr((string) $page->first(".//a[contains(@href, 'Magazins_Details')]", $block)?->getAttribute('href'), 'id='));

            // The old site lists some numbers twice (e.g. two different issues both labelled 105). Keep the first; let an editor decide.
            if (isset($seen[$number])) {
                $this->client->problems[] = "Issue \"{$label}\" appears twice (old ids {$seen[$number]} and {$oldId}); kept {$seen[$number]}, please check Magazins_Details.aspx?id={$oldId}";

                continue;
            }

            $seen[$number] = $oldId;
            $issue = Issue::updateOrCreate(['number' => $number], [
                'volume_id' => $volume->id,
                'title' => ['ar' => $label, 'en' => "Issue {$number}"],
                'is_published' => true,
            ]);

            $cover = $page->first(".//img[contains(@src, 'images/') and not(contains(@src, 'pdf.jpg'))]", $block);
            $this->attach($issue, 'cover', $page->absolute($cover?->getAttribute('src')));

            if ($this->withPdfs) {
                $pdf = $page->first(".//a[contains(@href, '.pdf')]", $block);
                $this->attach($issue, 'file', $page->absolute($pdf?->getAttribute('href')));
            }

            $this->count('issues');
        }

        // The newest issue is the one subscribers unlock.
        Issue::orderByDesc('number')->first()?->update(['is_current' => true]);
    }

    private function news(): void
    {
        // No list page on the old site.
        $this->probe('default.aspx', 'News_Details.aspx', 'news', function (int $id) {
            $page = $this->page("News_Details.aspx?id={$id}");
            $title = $page?->text("//span[contains(@id, 'Magazins_Details_Name_ARLabel_0')]");

            if (! $title) {
                return false;
            }

            $body = $page->first("//span[contains(@id, 'Magazins_Details_Name_ARLabel0_0')]");
            $news = JournalNews::updateOrCreate(['slug' => "news-{$id}"], [
                'title' => ['ar' => $title],
                'body' => self::both($body ? $this->rich(HtmlPage::innerHtml($body), $page->url) : null),
                'is_published' => true,
            ]);

            $image = $page->first("//img[contains(@id, 'DataList2_Image1')]");
            $this->attach($news, 'image', $page->absolute($image?->getAttribute('src')));
            $this->count('news');

            return true;
        });
    }

    private function pages(): void
    {
        $pages = [
            'about' => ['about.aspx', 'About the journal'],
            'publishing-rules' => ['Publishing_Rules.aspx', 'Publishing rules'],
            'call-for-papers' => ['Send_Research.aspx', 'Call for papers'],
        ];

        foreach ($pages as $slug => [$path, $english]) {
            $page = $this->page($path);
            $content = $page?->first('//div['.HtmlPage::hasClass('page-content').']');

            if (! $content) {
                continue;
            }

            // Drop the old site's form buttons and file pickers; keep the text.
            foreach ($page->all('.//input | .//button', $content) as $control) {
                $control->parentNode->removeChild($control);
            }

            Page::updateOrCreate(['site' => Site::Jac, 'slug' => $slug], [
                'title' => ['ar' => $page->text('//h1['.HtmlPage::hasClass('heading-title').']') ?? $english, 'en' => $english],
                'content' => ['ar' => $this->rich(HtmlPage::innerHtml($content), $page->url)],
                'is_published' => true,
            ]);
            $this->count('pages');
        }
    }
}
