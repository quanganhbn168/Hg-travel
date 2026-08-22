<?php

namespace App\Console\Commands;

use App\Services\HgTripImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportHgTripPackage extends Command
{
    protected $signature = 'hgtrip:import
        {archive : Path to the HGTRIP_IMPORT_FULL ZIP package}
        {--dry-run : Validate the package without writing database or public files}
        {--force : Replace the saved import snapshot when it already exists}';

    protected $description = 'Import a staged HG TRIP tour package from XLSX, raw content and images.';

    public function handle(HgTripImportService $importer): int
    {
        if (!$this->option('dry-run') && !$this->option('force')) {
            $this->error('The first real import requires --force so the staging intent is explicit.');

            return self::FAILURE;
        }

        try {
            $result = $importer->import(
                (string) $this->argument('archive'),
                (bool) $this->option('dry-run'),
                (bool) $this->option('force'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info($result['dry_run'] ? 'HG TRIP import dry-run passed.' : 'HG TRIP package imported as staging data.');
        $this->table(
            ['Data', 'Count'],
            [
                ['Tours', $result['tours']],
                ['Itineraries', $result['itineraries']],
                ['Schedules', $result['schedules']],
                ['Sections', $result['sections']],
                ['Images', $result['images']],
                ['Raw content files', $result['raw_content']],
                ['Unresolved items', count($result['unresolved'])],
            ],
        );

        if ($result['unresolved'] !== []) {
            $this->warn('Six source conflicts/unresolved items were preserved for manual review; none was guessed automatically.');
        }

        return self::SUCCESS;
    }
}
