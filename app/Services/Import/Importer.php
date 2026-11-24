<?php

namespace App\Services\Import;

use App\Support\Import\ContentCleaner;
use App\Support\Import\HtmlPage;
use Closure;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Throwable;

/** Shared plumbing for the one-off imports from the old ksaac.org and jac-kw.org sites. */
abstract class Importer
{
    /** Counts per content type, printed by the command. */
    public array $counts = [];

    public bool $withPdfs = false;

    public ?int $limit = null;

    /** @var Closure(string): void|null progress output */
    public ?Closure $output = null;

    public function __construct(protected LiveSiteClient $client) {}

    /** @return array<string, Closure> content type => import step */
    abstract public function steps(): array;

    abstract protected function base(): string;

    public function run(array $only = []): void
    {
        foreach ($this->steps() as $name => $step) {
            if ($only && ! in_array($name, $only, true)) {
                continue;
            }

            $this->say("Importing {$name}…");
            $step();
        }
    }

    /** True while probing old ids: deleted items answer 404/500 there, which is expected. */
    private bool $probing = false;

    protected function page(string $path): ?HtmlPage
    {
        return $this->client->page($this->base().'/'.ltrim($path, '/'), optional: $this->probing);
    }

    protected function count(string $type): void
    {
        $this->counts[$type] = ($this->counts[$type] ?? 0) + 1;
    }

    protected function say(string $message): void
    {
        $this->output && ($this->output)($message);
    }

    /**
     * Ids to probe for a detail page that has no list page: 1 up to the highest id linked from the homepage,
     * then on until 8 ids in a row are empty (old ids have gaps where items were deleted).
     *
     * @param  Closure(int): bool  $import  imports one id, returns false when the page is empty
     */
    protected function probe(string $homePath, string $detailPage, string $type, Closure $import): void
    {
        preg_match_all('/'.preg_quote($detailPage, '/').'\?id=(\d+)/i', (string) $this->page($homePath)?->html, $m);
        $highestKnown = (int) max([0, ...$m[1]]);
        $misses = 0;
        $this->probing = true;

        try {
            for ($id = 1; ($id <= $highestKnown || $misses < 8) && ! $this->reachedLimit($type); $id++) {
                $misses = $import($id) ? 0 : $misses + 1;
            }
        } finally {
            $this->probing = false;
        }
    }

    protected function reachedLimit(string $type): bool
    {
        return $this->limit !== null && ($this->counts[$type] ?? 0) >= $this->limit;
    }

    /** Clean Word HTML; images and documents inside it are copied to this site's public storage. */
    protected function rich(?string $html, string $pageUrl): ?string
    {
        return $html === null ? null : (new ContentCleaner(fn (string $url) => $this->publicCopy($url)))->clean($html, $pageUrl);
    }

    /**
     * Copy a file from the old site to public storage (once) and return its new URL.
     * Documents (PDF, Word…) are only copied with --with-pdfs; otherwise the link keeps pointing at the old site
     * and a later run with the option replaces it.
     */
    protected function publicCopy(string $url): ?string
    {
        if (! $this->withPdfs && self::isDocument($url)) {
            return $url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $path = 'imported/'.str_replace('www.', '', (string) $host).'/'.basename((string) parse_url($url, PHP_URL_PATH));
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $tmp = $this->client->download($url);

            if (! $tmp) {
                return null;
            }

            $disk->put($path, file_get_contents($tmp));
            @unlink($tmp);
        }

        return $disk->url($path);
    }

    /**
     * Attach a file from the old site to a media collection unless the collection already has it.
     * Collections named here take images only (the old site sometimes points a "cover" at a PDF).
     * A failure is reported and the import carries on.
     */
    protected function attach(HasMedia $model, string $collection, ?string $url): void
    {
        if (! $url || $model->getMedia($collection)->contains(fn ($m) => $m->getCustomProperty('source') === $url)) {
            return;
        }

        $imageOnly = in_array($collection, ['cover', 'image', 'gallery', 'pages', 'logo', 'photo'], true);

        if ($imageOnly && ! preg_match('~\.(jpe?g|png|gif|webp)(\?|$)~i', $url)) {
            $this->client->problems[] = "Not an image, skipped as {$collection}: {$url}";

            return;
        }

        try {
            if ($tmp = $this->client->download($url)) {
                $model->addMedia($tmp)
                    ->usingFileName(basename((string) parse_url($url, PHP_URL_PATH)))
                    ->withCustomProperties(['source' => $url])
                    ->toMediaCollection($collection);
            }
        } catch (Throwable $e) {
            $this->client->problems[] = "Could not attach {$url} as {$collection}: {$e->getMessage()}";
        }
    }

    protected static function isDocument(string $url): bool
    {
        return (bool) preg_match('~\.(pdf|docx?|xlsx?|pptx?)(\?|$)~i', $url);
    }

    /** The old English pages often repeat the Arabic text; keep English only when it is mostly Latin letters. */
    protected static function english(?string $text): ?string
    {
        $plain = strip_tags((string) $text);
        $latin = preg_match_all('/[A-Za-z]/', $plain);
        $arabic = preg_match_all('/\p{Arabic}/u', $plain);

        return $latin > 0 && $latin > $arabic ? $text : null;
    }

    /** Bilingual value for a translatable column, leaving out a missing language. */
    protected static function both(?string $ar, ?string $en = null): array
    {
        return array_filter(['ar' => $ar, 'en' => self::english($en)], fn ($v) => filled($v));
    }
}
