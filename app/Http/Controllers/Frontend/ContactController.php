<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Services\ProductLineService;
use App\Services\TravelServiceCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(TravelServiceCatalog $serviceCatalog, ProductLineService $productLineService): View
    {
        return view('frontend.contact', [
            'contactSubjects' => collect($productLineService->contactSubjects())
                ->merge($serviceCatalog->contactSubjects())
                ->unique()
                ->values()
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactSubmission::create($data + ['status' => 'new']);

        return to_route('contact')->with('success', 'HG đã nhận được lời nhắn. Đội ngũ tư vấn sẽ phản hồi sớm nhất.');
    }
}
