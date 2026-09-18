<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexContactSubmissionRequest;
use App\Http\Requests\Admin\UpdateContactSubmissionRequest;
use App\Models\ContactSubmission;
use App\Services\ContactSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactSubmissionController extends Controller
{
    public function __construct(private readonly ContactSubmissionService $submissions) {}

    public function index(IndexContactSubmissionRequest $request): View
    {
        return view(
            'admin.contact_submissions.index',
            $this->submissions->indexContext($request->validated()),
        );
    }

    public function edit(ContactSubmission $contactSubmission): View
    {
        return view('admin.contact_submissions.edit', compact('contactSubmission'));
    }

    public function update(
        UpdateContactSubmissionRequest $request,
        ContactSubmission $contactSubmission,
    ): RedirectResponse {
        $this->submissions->update($contactSubmission, $request->validated());

        return back()->with('success', 'Đã cập nhật yêu cầu liên hệ.');
    }
}
