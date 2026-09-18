<?php

namespace App\Services;

use App\Models\TravelMoment;
use App\Models\TravelMomentGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TravelMomentService
{
    public function __construct(private readonly MediaReferenceService $mediaReferences) {}

    public function indexContext(array $filters): array
    {
        return [
            'moments' => $this->paginate($filters),
            'groups' => $this->groups(),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = TravelMoment::query()
            ->with('group')
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('title', 'like', '%'.trim($filters['search']).'%')
                        ->orWhere('slug', 'like', '%'.trim($filters['search']).'%')
                )
            )
            ->when(
                filled($filters['group_id'] ?? null),
                fn ($query) => $query->where('group_id', $filters['group_id'])
            )
            ->when(
                ($filters['status'] ?? null) === 'active',
                fn ($query) => $query->where('is_active', true)
            )
            ->when(
                ($filters['status'] ?? null) === 'inactive',
                fn ($query) => $query->where('is_active', false)
            )
            ->orderBy('sort_order')
            ->latest('id');

        $paginator = $query
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $paginator->getCollection()->each(function (TravelMoment $moment): void {
            $moment->setAttribute(
                'image_preview_url',
                $this->mediaReferences->url($moment->image_url, null, true),
            );
        });

        return $paginator;
    }

    public function formContext(?TravelMoment $moment = null): array
    {
        return [
            'moment' => $moment ?: new TravelMoment(['is_active' => true]),
            'groups' => $this->groups(),
        ];
    }

    public function create(array $data): TravelMoment
    {
        return TravelMoment::create($this->payload($data));
    }

    public function update(TravelMoment $moment, array $data): void
    {
        $moment->update($this->payload($data, $moment));
    }

    public function delete(TravelMoment $moment): void
    {
        $moment->delete();
    }

    private function groups()
    {
        return TravelMomentGroup::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function payload(array $data, ?TravelMoment $moment = null): array
    {
        $image = $this->mediaReferences->field(
            $data,
            'image_url',
            $moment?->image_url,
        );

        if (blank($image)) {
            throw ValidationException::withMessages([
                'image_url' => 'Khoảnh khắc cần có ảnh.',
            ]);
        }

        return [
            'group_id' => (int) $data['group_id'],
            'title' => trim($data['title']),
            'slug' => $data['slug'],
            'image_url' => $image,
            'alt_text' => $data['alt_text'] ?? $data['title'],
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
