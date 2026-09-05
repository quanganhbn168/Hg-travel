<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckMediaStorage extends Command
{
    protected $signature = 'media:check';

    protected $description = 'Verify configured media storage with a write/read/delete probe and report PHP limits.';

    public function handle(): int
    {
        $disk = Storage::disk('public_media');
        $path = '.probe-'.Str::uuid();
        try {
            $disk->put($path, $path);
            if ($disk->get($path) !== $path) {
                throw new \RuntimeException('Media read/write mismatch.');
            }
            $this->info('Storage writable: '.config('filesystems.disks.public_media.root'));
            $this->line('upload_max_filesize='.ini_get('upload_max_filesize').'; post_max_size='.ini_get('post_max_size').'; GD='.(extension_loaded('gd') ? 'yes' : 'no'));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
