<?php

namespace App\Services;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactSubmissionService
{
    public const STATUSES = [
        'new' => 'Mới',
        'processing' => 'Đang xử lý',
        'resolved' => 'Đã xử lý',
        'closed' => 'Đã đóng',
    ];

    public function indexContext(array $filters): array
    {
        return [
            'submissions' => $this->paginate($filters),
            'statuses' => self::STATUSES,
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return ContactSubmission::query()
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                )
            )
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function update(ContactSubmission $submission, array $data): void
    {
        $submission->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
        ]);
    }
}
