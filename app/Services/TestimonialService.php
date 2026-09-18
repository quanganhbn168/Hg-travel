<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TestimonialService
{
    public function __construct(private readonly MediaReferenceService $mediaReferences) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        $paginator = Testimonial::query()
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                )
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
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $paginator->getCollection()->each(function (Testimonial $testimonial): void {
            $testimonial->setAttribute(
                'avatar_url',
                $this->mediaReferences->url($testimonial->avatar_path, null, true),
            );
        });

        return $paginator;
    }

    public function formContext(?Testimonial $testimonial = null): array
    {
        return [
            'testimonial' => $testimonial ?: new Testimonial([
                'rating' => 5,
                'is_active' => true,
            ]),
        ];
    }

    public function save(array $data, ?Testimonial $testimonial = null): Testimonial
    {
        $testimonial ??= new Testimonial;

        $data['avatar_path'] = $this->mediaReferences->field(
            $data,
            'avatar_path',
            $testimonial->avatar_path,
        );

        unset($data['avatar_path_remove']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $testimonial->fill($data)->save();

        return $testimonial;
    }

    public function delete(Testimonial $testimonial): void
    {
        $testimonial->delete();
    }
}
