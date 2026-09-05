<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupMedia extends Command
{
    protected $signature = 'media:backup';

    protected $description = 'Back up the database and media with a SHA256 inventory before migration.';

    public function handle(): int
    {
        $directory = storage_path('app/private/media-backups/'.now()->format('Ymd_His').'-'.Str::lower(Str::random(6)));
        File::ensureDirectoryExists($directory);
        $database = config('database.connections.'.config('database.default'));

        if ($database['driver'] !== 'mysql') {
            $this->error('This command requires MySQL and mysqldump; back up other databases with their native tools.');

            return self::FAILURE;
        }

        $process = new Process([
            'mysqldump', '--single-transaction', '--no-tablespaces', '--skip-lock-tables',
            '--host='.$database['host'], '--port='.$database['port'], '--user='.$database['username'],
            '--result-file='.$directory.'/database.sql', $database['database'],
        ], base_path(), ['MYSQL_PWD' => (string) $database['password']], timeout: 300);
        $process->mustRun();
        $root = config('filesystems.disks.public_media.root');
        File::copyDirectory($root, $directory.'/media');
        $inventory = [];
        foreach (File::allFiles($root) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $hash = hash_file('sha256', $file->getPathname());
            if ($hash !== hash_file('sha256', $directory.'/media/'.$relative)) {
                throw new \RuntimeException('Backup mismatch: '.$relative);
            }
            $inventory[$relative] = ['size' => $file->getSize(), 'sha256' => $hash];
        }
        File::put($directory.'/inventory.json', json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $generated = [];
        foreach (['favicon-master.png', 'favicon.svg', 'favicon.ico', 'favicon-16x16.png', 'favicon-32x32.png', 'apple-touch-icon.png', 'android-chrome-192x192.png', 'android-chrome-512x512.png', 'site.webmanifest'] as $filename) {
            if (! is_file(public_path($filename))) {
                continue;
            }
            File::ensureDirectoryExists($directory.'/generated-assets');
            File::copy(public_path($filename), $directory.'/generated-assets/'.$filename);
            $hash = hash_file('sha256', public_path($filename));
            if ($hash !== hash_file('sha256', $directory.'/generated-assets/'.$filename)) {
                throw new \RuntimeException('Generated asset backup mismatch: '.$filename);
            }
            $generated[$filename] = $hash;
        }
        File::put($directory.'/generated-assets.json', json_encode($generated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Verified backup: '.$directory.' ('.count($inventory).' files)');

        return self::SUCCESS;
    }
}
