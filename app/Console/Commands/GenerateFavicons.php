<?php

namespace App\Console\Commands;

use App\Services\FaviconService;
use Illuminate\Console\Command;

class GenerateFavicons extends Command
{
    protected $signature = 'favicon:generate {source? : Absolute path to a PNG, JPG, WEBP or GIF master image}';

    protected $description = 'Generate the fixed public favicon file set from the favicon master image.';

    public function handle(FaviconService $favicons): int
    {
        $source = $this->argument('source')
            ?: (is_file(public_path('favicon-master.png'))
                ? public_path('favicon-master.png')
                : public_path('images/logo-hg.png'));

        $result = $favicons->generateFromPath($source);

        $this->components->info(sprintf('Generated %d favicon files from %s.', count($result['files']), $source));

        return self::SUCCESS;
    }
}
