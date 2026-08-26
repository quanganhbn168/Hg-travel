<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessSettingsRequest;
use App\Http\Requests\Admin\UpdateContactSettingsRequest;
use App\Http\Requests\Admin\UpdateMediaSettingsRequest;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Http\Requests\Admin\UpdateTourSettingsRequest;
use App\Http\Requests\Admin\UpdateWebsiteSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.settings.website');
    }

    public function website(): View
    {
        return view('admin.settings.website', ['settings' => $this->settingService->website()]);
    }

    public function updateWebsite(UpdateWebsiteSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateWebsite($request->validated());

        return back()->with('success', 'Đã lưu cài đặt website.');
    }

    public function business(): View
    {
        return view('admin.settings.business', ['settings' => $this->settingService->business()]);
    }

    public function updateBusiness(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateBusiness($request->validated());

        return back()->with('success', 'Đã lưu cài đặt doanh nghiệp.');
    }

    public function media(): View
    {
        return view('admin.settings.media', ['settings' => $this->settingService->media()]);
    }

    public function updateMedia(UpdateMediaSettingsRequest $request): RedirectResponse
    {
        $faviconGenerated = $this->settingService->updateMedia($request->validated());

        return back()->with('success', $faviconGenerated
            ? 'Đã lưu cài đặt media và tạo bộ favicon chuẩn.'
            : 'Đã lưu cài đặt media.');
    }

    public function seo(): View
    {
        return view('admin.settings.seo', ['settings' => $this->settingService->seo()]);
    }

    public function updateSeo(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateSeo($request->validated());

        return back()->with('success', 'Đã lưu cài đặt SEO.');
    }

    public function contact(): View
    {
        return view('admin.settings.contact', ['settings' => $this->settingService->contact()]);
    }

    public function updateContact(UpdateContactSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateContact($request->validated());

        return back()->with('success', 'Đã lưu cài đặt liên lạc.');
    }

    public function tour(): View
    {
        return view('admin.settings.tour', ['settings' => $this->settingService->tour()]);
    }

    public function updateTour(UpdateTourSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateTour([
            ...$request->validated(),
            'show_schedules' => $request->boolean('show_schedules'),
            'show_seat_availability' => $request->boolean('show_seat_availability'),
        ]);

        return back()->with('success', 'Đã lưu cài đặt tour.');
    }
}
