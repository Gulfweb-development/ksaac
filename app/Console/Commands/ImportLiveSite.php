<?php

namespace App\Console\Commands;

use App\Services\Import\JacImporter;
use App\Services\Import\KsaacImporter;
use App\Services\Import\LiveSiteClient;
use Illuminate\Console\Command;

/**
 * One-off migration of content from the old sites. Safe to re-run: records are matched on stable keys
 * (old ids, issue/volume numbers, page slugs) and files already attached are not downloaded again.
 */
class ImportLiveSite extends Command
{
    protected $signature = 'import:live
        {site : ksaac or jac}
        {--only= : Comma-separated parts, e.g. news,publications (default: all)}
        {--with-pdfs : Also download publication and issue PDFs (large)}
        {--limit= : Import at most this many items per part (for trial runs)}
        {--fresh : Ignore the local page cache and fetch everything again}';

    protected $description = 'Import content from the old ksaac.org or jac-kw.org website';

    public function handle(LiveSiteClient $client): int
    {
        $importer = match ($this->argument('site')) {
            'ksaac' => new KsaacImporter($client),
            'jac' => new JacImporter($client),
            default => null,
        };

        if (! $importer) {
            $this->error('Site must be "ksaac" or "jac".');

            return self::FAILURE;
        }

        $client->fresh = (bool) $this->option('fresh');
        $importer->withPdfs = (bool) $this->option('with-pdfs');
        $importer->limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $importer->output = fn (string $message) => $this->info($message);

        $importer->run(array_filter(explode(',', (string) $this->option('only'))));

        $this->table(['Imported', 'Count'], collect($importer->counts)->map(fn ($n, $type) => [$type, $n])->values());

        if ($client->problems) {
            $this->warn(count($client->problems).' problem(s):');
            collect($client->problems)->each(fn ($p) => $this->line('  - '.$p));
        }

        return self::SUCCESS;
    }
}
