<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourImage;
use App\Models\TourInclusion;
use App\Models\TourSchedule;
use App\Models\TourSection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TourService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Tour::with(['categories', 'destinations'])->orderBy('sort_order')->orderByDesc('id');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== '') $query->where('is_active', (bool) $filters['active']);
        return $query->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();
    }

    public function formContext(?Tour $tour = null): array
    {
        if ($tour) {
            $tour->loadMissing([
                'categories',
                'destinations',
                'itineraries',
                'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order'),
                'schedules' => fn ($query) => $query->orderBy('departure_date'),
                'sections',
                'inclusions',
            ]);
        }

        return [
            'tour' => $tour ?: new Tour(['status' => 'draft', 'currency' => 'VND', 'is_active' => true, 'booking_open' => true]),
            'categories' => TourCategory::where('is_active', true)->orderBy('name')->get(),
            'destinations' => Destination::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    public function create(array $data): Tour
    {
        return DB::transaction(function () use ($data): Tour {
            $tour = Tour::create($this->payload($data));
            $this->syncCategories($tour, $data['tour_category_ids'] ?? []);
            $this->syncDestinations($tour, $data['destination_ids'] ?? []);
            $this->syncItineraries($tour, $data['itineraries'] ?? []);
            $this->syncImages($tour, $data);
            $this->syncSchedules($tour, $data['schedules'] ?? []);
            $this->syncSections($tour, $data['sections'] ?? []);
            $this->syncInclusions($tour, $data['inclusions'] ?? []);

            return $tour;
        });
    }

    public function update(Tour $tour, array $data): void
    {
        DB::transaction(function () use ($tour, $data): void {
            $tour->update($this->payload($data));
            $this->syncCategories($tour, $data['tour_category_ids'] ?? []);
            $this->syncDestinations($tour, $data['destination_ids'] ?? []);
            $this->syncItineraries($tour, $data['itineraries'] ?? []);
            $this->syncImages($tour, $data);
            $this->syncSchedules($tour, $data['schedules'] ?? []);
            $this->syncSections($tour, $data['sections'] ?? []);
            $this->syncInclusions($tour, $data['inclusions'] ?? []);
        });
    }
    public function delete(Tour $tour): void { $tour->delete(); }
    private function payload(array $data): array { return ['code' => trim($data['code']), 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'summary' => $data['summary'] ?? null, 'description' => $data['description'] ?? null, 'duration_days' => (int) $data['duration_days'], 'duration_nights' => (int) ($data['duration_nights'] ?? 0), 'transport' => filled($data['transport'] ?? null) ? trim($data['transport']) : null, 'starting_price' => $data['starting_price'], 'currency' => strtoupper($data['currency']), 'max_guests' => $data['max_guests'] ?? null, 'status' => $data['status'], 'is_featured' => (bool) ($data['is_featured'] ?? false), 'is_active' => (bool) ($data['is_active'] ?? false), 'booking_open' => (bool) ($data['booking_open'] ?? false), 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'published_at' => $data['published_at'] ?? null, 'sort_order' => (int) ($data['sort_order'] ?? 0)]; }

    /** @param array<int, int|string> $categoryIds */
    private function syncCategories(Tour $tour, array $categoryIds): void
    {
        $sync = [];

        foreach (array_values(array_unique(array_filter($categoryIds))) as $index => $categoryId) {
            $sync[(int) $categoryId] = ['sort_order' => $index + 1];
        }

        $tour->categories()->sync($sync);
    }

    /** @param array<int, int|string> $destinationIds */
    private function syncDestinations(Tour $tour, array $destinationIds): void
    {
        $sync = [];

        foreach (array_values(array_unique(array_filter($destinationIds))) as $index => $destinationId) {
            $sync[(int) $destinationId] = ['sort_order' => $index + 1];
        }

        $tour->destinations()->sync($sync);
    }

    /** @param array<int, array<string, mixed>> $itineraries */
    private function syncItineraries(Tour $tour, array $itineraries): void
    {
        $tour->itineraries()->delete();

        foreach (array_values($itineraries) as $index => $itinerary) {
            $tour->itineraries()->create([
                'day_number' => (int) $itinerary['day_number'],
                'title' => trim($itinerary['title']),
                'description' => $itinerary['description'] ?? null,
                'meals' => filled($itinerary['meals'] ?? null) ? trim($itinerary['meals']) : null,
                'accommodation' => filled($itinerary['accommodation'] ?? null) ? trim($itinerary['accommodation']) : null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function syncImages(Tour $tour, array $data): void
    {
        $removeIds = collect($data['remove_image_ids'] ?? [])
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($removeIds->isNotEmpty()) {
            $tour->images()->whereIn('id', $removeIds)->delete();
        }

        if ((bool) ($data['cover_image_remove'] ?? false)) {
            $tour->images()->where('is_cover', true)->delete();
            $tour->clearMediaCollection('tour_images');
        }

        $coverPath = trim((string) ($data['cover_image'] ?? ''));
        if ($coverPath !== '') {
            $tour->clearMediaCollection('tour_images');
            $tour->images()->update(['is_cover' => false]);
            $tour->images()->create([
                'path' => $coverPath,
                'alt_text' => $tour->name,
                'is_cover' => true,
                'sort_order' => 0,
            ]);
        } elseif (filled($data['cover_image_id'] ?? null)) {
            $cover = $tour->images()->whereKey((int) $data['cover_image_id'])->first();

            if ($cover) {
                $tour->clearMediaCollection('tour_images');
                $tour->images()->update(['is_cover' => false]);
                $cover->update(['is_cover' => true, 'sort_order' => 0]);
            }
        }

        $galleryPaths = collect(explode('|', (string) ($data['gallery_images'] ?? '')))
            ->map(fn (string $path): string => trim($path))
            ->filter()
            ->unique()
            ->values();

        $nextSortOrder = (int) $tour->images()->max('sort_order') + 1;
        foreach ($galleryPaths as $path) {
            $tour->images()->create([
                'path' => $path,
                'alt_text' => $tour->name,
                'is_cover' => false,
                'sort_order' => $nextSortOrder++,
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $schedules */
    private function syncSchedules(Tour $tour, array $schedules): void
    {
        $existingSchedules = $tour->schedules()->withCount('bookingItems')->get()->keyBy('id');

        foreach (array_values($schedules) as $index => $scheduleData) {
            $scheduleId = filled($scheduleData['id'] ?? null) ? (int) $scheduleData['id'] : null;
            $schedule = $scheduleId ? $existingSchedules->get($scheduleId) : null;

            if ($scheduleId && ! $schedule) {
                throw ValidationException::withMessages(['schedules.'.$index.'.id' => 'Lịch khởi hành không thuộc tour này.']);
            }

            if ((bool) ($scheduleData['remove'] ?? false)) {
                if ($schedule?->booking_items_count > 0) {
                    throw ValidationException::withMessages(['schedules.'.$index.'.remove' => 'Lịch này đã có booking; hãy chuyển trạng thái sang Đóng hoặc Hủy thay vì xóa.']);
                }

                $schedule?->delete();
                continue;
            }

            $seatsTotal = (int) $scheduleData['seats_total'];
            $reserved = (int) ($schedule?->seats_reserved ?? 0);
            if ($seatsTotal > 0 && $seatsTotal < $reserved) {
                throw ValidationException::withMessages(['schedules.'.$index.'.seats_total' => 'Số chỗ tổng không thể thấp hơn '.$reserved.' chỗ đã giữ.']);
            }

            $price = (float) $scheduleData['price'];
            $salePrice = filled($scheduleData['sale_price'] ?? null) ? (float) $scheduleData['sale_price'] : null;
            if ($salePrice !== null && $salePrice > 0 && $price > 0 && $salePrice > $price) {
                throw ValidationException::withMessages(['schedules.'.$index.'.sale_price' => 'Giá giảm không thể cao hơn giá niêm yết của ngày khởi hành này.']);
            }

            $payload = [
                'departure_date' => $scheduleData['departure_date'],
                'return_date' => $scheduleData['return_date'] ?? null,
                'seats_total' => $seatsTotal,
                'price' => $price,
                'sale_price' => $salePrice && $salePrice > 0 ? $salePrice : null,
                'transport' => filled($scheduleData['transport'] ?? null) ? trim((string) $scheduleData['transport']) : null,
                'status' => $scheduleData['status'],
                'notes' => filled($scheduleData['notes'] ?? null) ? trim((string) $scheduleData['notes']) : null,
            ];

            if ($schedule) {
                $schedule->update($payload);
            } else {
                $tour->schedules()->create($payload + ['seats_reserved' => 0]);
            }
        }
    }

    /** @param array<int, array<string, mixed>> $sections */
    private function syncSections(Tour $tour, array $sections): void
    {
        $existingSections = $tour->sections()->get()->keyBy('id');

        foreach (array_values($sections) as $index => $sectionData) {
            $sectionId = filled($sectionData['id'] ?? null) ? (int) $sectionData['id'] : null;
            $section = $sectionId ? $existingSections->get($sectionId) : null;

            if ($sectionId && ! $section) {
                throw ValidationException::withMessages(['sections.'.$index.'.id' => 'Nội dung này không thuộc tour hiện tại.']);
            }

            if ((bool) ($sectionData['remove'] ?? false)) {
                $section?->delete();
                continue;
            }

            $payload = [
                'type' => filled($sectionData['type'] ?? null) ? trim((string) $sectionData['type']) : 'other',
                'title' => filled($sectionData['title'] ?? null) ? trim((string) $sectionData['title']) : null,
                'content' => $sectionData['content'] ?? null,
                'sort_order' => $index + 1,
            ];

            if ($section) {
                $section->update($payload);
            } else {
                $tour->sections()->create($payload);
            }
        }
    }

    /** @param array<int, array<string, mixed>> $inclusions */
    private function syncInclusions(Tour $tour, array $inclusions): void
    {
        $tour->inclusions()->delete();

        foreach (array_values($inclusions) as $index => $inclusion) {
            $tour->inclusions()->create([
                'type' => $inclusion['type'],
                'content' => trim($inclusion['content']),
                'sort_order' => $index + 1,
            ]);
        }
    }
}
