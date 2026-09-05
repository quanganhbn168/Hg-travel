<?php

namespace App\Jobs;

use App\Services\MediaOptimizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OptimizeMedia implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $mediaId)
    {
        $this->onQueue(config('media.queue'));
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(MediaOptimizationService $optimizer): void
    {
        if ($media = Media::find($this->mediaId)) {
            $optimizer->optimize($media);
        }
    }
}
