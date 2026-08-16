<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexServiceRequest;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Services\ServiceCatalogAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceCatalogAdminService $catalog) {}
    public function index(IndexServiceRequest $request): View { return view('admin.services.index', ['services' => $this->catalog->services($request->validated()), 'categories' => \App\Models\ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get()]); }
    public function create(): View { return view('admin.services.create', $this->catalog->serviceContext()); }
    public function store(StoreServiceRequest $request): RedirectResponse { $service = $this->catalog->createService($request->validated()); return to_route('admin.services.edit', $service)->with('success', 'Đã tạo dịch vụ.'); }
    public function edit(Service $service): View { return view('admin.services.edit', $this->catalog->serviceContext($service)); }
    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse { $this->catalog->updateService($service, $request->validated()); return back()->with('success', 'Đã cập nhật dịch vụ.'); }
    public function destroy(Service $service): RedirectResponse { $this->catalog->deleteService($service); return to_route('admin.services.index')->with('success', 'Đã xóa dịch vụ.'); }
}
