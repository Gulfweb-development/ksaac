<?php

namespace App\Services\Import;

use App\Support\Import\HtmlPage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Fetches pages and files from the old sites. Pages are cached on the private disk so re-running an import
 * (or a test) does not hit the live site again; --fresh bypasses the cache.
 */
class LiveSiteClient
{
    private const CACHE_DIR = 'import-cache';

    public bool $fresh = false;

    /** Missing pages, failed downloads etc., reported at the end of a run. */
    public array $problems = [];

    /** @param  bool  $optional  a missing page is expected (probing old ids), not a problem to report */
    public function page(string $url, bool $optional = false): ?HtmlPage
    {
        $key = self::CACHE_DIR.'/pages/'.md5($url).'.html';
        $disk = Storage::disk('local');

        if (! $this->fresh && $disk->exists($key)) {
            return new HtmlPage($disk->get($key), $url);
        }

        $response = $this->http()->get($url);

        if (! $response->successful()) {
            $optional || $this->problems[] = "HTTP {$response->status()}: {$url}";

            return null;
        }

        $disk->put($key, $response->body());

        return new HtmlPage($response->body(), $url);
    }

    /** Download a file to a temporary local path (for addMedia). Null when it is missing on the old site. */
    public function download(string $url): ?string
    {
        $key = self::CACHE_DIR.'/files/'.md5($url).'-'.basename(parse_url($url, PHP_URL_PATH) ?: 'file');
        $disk = Storage::disk('local');

        if ($this->fresh || ! $disk->exists($key)) {
            $response = $this->http()->timeout(180)->get($url);

            if (! $response->successful() || $response->body() === '') {
                $this->problems[] = "Download failed ({$response->status()}): {$url}";

                return null;
            }

            $disk->put($key, $response->body());
        }

        // addMedia moves the file, so hand over a copy and keep the cache.
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'-'.basename($key);
        copy($disk->path($key), $tmp) ?: throw new RuntimeException("Cannot copy {$key}");

        return $tmp;
    }

    private function http()
    {
        return Http::withHeaders(['User-Agent' => 'KSAAC site migration'])->timeout(60)->retry(2, 1000, throw: false);
    }
}
