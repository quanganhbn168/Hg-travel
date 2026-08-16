<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactSubmissionController extends Controller
{
    public function index(Request $request): View { $query = ContactSubmission::query()->latest(); if ($request->filled('status')) $query->where('status', $request->string('status')); return view('admin.contact_submissions.index', ['submissions' => $query->paginate(20)->withQueryString()]); }
    public function edit(ContactSubmission $contactSubmission): View { return view('admin.contact_submissions.edit', compact('contactSubmission')); }
    public function update(Request $request, ContactSubmission $contactSubmission): RedirectResponse { $data = $request->validate(['status' => ['required','in:new,processing,resolved,closed'], 'admin_note' => ['nullable','string','max:5000']]); $contactSubmission->update($data); return back()->with('success', 'Đã cập nhật yêu cầu liên hệ.'); }
}
