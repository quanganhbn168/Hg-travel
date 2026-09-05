<?php

namespace App\Console\Commands;

use App\Jobs\OptimizeMedia;
use App\Models\SiteAsset;
use App\Services\MediaReferenceService;
use App\Services\SiteSettingsService;
use App\Support\MediaFields;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AuditMedia extends Command
{
    protected $signature = 'media:audit {--adopt : Register existing media files and populate references without moving files} {--optimize : Queue conversions for registered images} {--json : Output JSON}';

    protected $description = 'Audit media references, and optionally adopt legacy local media without changing URLs or bytes.';

    public function handle(MediaReferenceService $references): int
    {
        $report = ['checked' => 0, 'local_valid' => 0, 'external' => 0, 'adopted' => 0, 'missing' => []];
        foreach (MediaFields::IMAGES as $class => $fields) {
            $table = (new $class)->getTable();
            foreach (DB::table($table)->select(['id', ...array_keys($fields)])->orderBy('id')->cursor() as $row) {
                foreach ($fields as $field => $idColumn) {
                    $result = $this->inspect($row->$field, $table.'#'.$row->id.'.'.$field, $report, $references);
                    if ($this->option('adopt') && $result !== null) {
                        DB::table($table)->where('id', $row->id)->update([$field => $result['path'], $idColumn => $result['id']]);
                    }
                }
            }
        }
        $settings = app(SiteSettingsService::class)->media();
        $ids = $settings->media_ids;
        foreach (MediaFields::SETTINGS as $field) {
            $result = $this->inspect($settings->$field, 'settings.media.'.$field, $report, $references);
            if ($this->option('adopt') && $result !== null) {
                $settings->$field = $result['path'];
                $ids[$field] = $result['id'];
            }
        }
        if ($this->option('adopt')) {
            $settings->media_ids = $ids;
            $settings->save();
        }
        if ($this->option('optimize')) {
            Media::whereNull('custom_properties->optimized_at')->eachById(fn ($media) => OptimizeMedia::dispatch($media->id));
        }
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Metric', 'Value'], collect($report)->except('missing')->map(fn ($value, $key) => [$key, $value])->values()->all());
            foreach ($report['missing'] as $missing) {
                $this->warn($missing['field'].' = '.$missing['path']);
            }
        }

        return $report['missing'] === [] ? self::SUCCESS : self::FAILURE;
    }

    private function inspect(?string $value, string $field, array &$report, MediaReferenceService $references): ?array
    {
        if (blank($value)) {
            return null;
        }
        $report['checked']++;
        $path = $references->localPath($value);
        if ($path === null) {
            $report['external']++;

            return null;
        }
        $managed = str_starts_with($path, 'media/');
        $disk = Storage::disk('public_media');
        $absolute = $managed ? $disk->path(substr($path, 6)) : public_path($path);
        if (! is_file($absolute) || ! @getimagesize($absolute)) {
            $report['missing'][] = ['field' => $field, 'path' => $value];

            return null;
        }
        $report['local_valid']++;
        if (! $this->option('adopt') || ! $managed) {
            return null;
        }
        $media = $references->find($path);
        if (! $media) {
            $owner = SiteAsset::firstOrCreate(['key' => 'library']);
            $info = getimagesize($absolute);
            $media = $owner->media()->create([
                'collection_name' => 'library', 'name' => pathinfo($absolute, PATHINFO_FILENAME),
                'file_name' => basename($absolute), 'mime_type' => $info['mime'], 'disk' => 'public_media',
                'conversions_disk' => 'public_media', 'size' => filesize($absolute), 'manipulations' => [],
                'custom_properties' => ['state' => 'ready', 'imported_path' => substr($path, 6), 'width' => $info[0], 'height' => $info[1], 'original_sha256' => hash_file('sha256', $absolute)],
                'generated_conversions' => [], 'responsive_images' => [],
            ]);
            $references->forget();
            $report['adopted']++;
        }

        return ['path' => $references->originalPath($media), 'id' => $media->id];
    }
}
