<?php

namespace App\Console\Commands;

use App\Services\MediaUsageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CleanupPendingMedia extends Command
{
    protected $signature = 'media:cleanup {--apply : Delete only expired, unreferenced pending uploads}';

    protected $description = 'Report abandoned pending uploads; deletion requires --apply. Library and archived files are retained.';

    public function handle(MediaUsageService $usage): int
    {
        $candidates = Media::where('custom_properties->state', 'pending')->where('created_at', '<', now()->subHours(config('media.pending_hours')))->pluck('id');
        foreach ($candidates as $id) {
            DB::transaction(function () use ($id, $usage): void {
                $media = Media::whereKey($id)->lockForUpdate()->first();
                if (! $media || $media->getCustomProperty('state') !== 'pending' || $usage->usages($media) !== []) {
                    return;
                }
                $this->line(($this->option('apply') ? 'DELETE ' : 'DRY RUN ').$id.' '.$media->file_name);
                if (! $this->option('apply')) {
                    return;
                }
                // Delete only the exact derivatives; never recursively delete shared imported directories.
                foreach (['webp_path', 'thumbnail_path'] as $key) {
                    if ($path = $media->getCustomProperty($key)) {
                        Storage::disk($media->disk)->delete($path);
                    }
                }
                $media->delete();
            });
        }

        return self::SUCCESS;
    }
}
