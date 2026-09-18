<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutPageRequest;
use App\Services\AboutPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __construct(private readonly AboutPageService $about) {}

    public function edit(): View
    {
        return view('admin.about.edit', $this->about->adminContext());
    }

    public function update(UpdateAboutPageRequest $request): RedirectResponse
    {
        $this->about->updateFromAdmin($request->validated());

        return back()->with('success', 'Đã cập nhật trang Giới thiệu.');
    }
}
