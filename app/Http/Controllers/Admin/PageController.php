<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPageRequest;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageService $pageService) {}
    public function index(IndexPageRequest $request): View { return view('admin.pages.index', ['pages' => $this->pageService->paginate($request->validated())]); }
    public function create(): View { return view('admin.pages.create', $this->pageService->formContext()); }
    public function store(StorePageRequest $request): RedirectResponse { $page = $this->pageService->create($request->validated()); return redirect()->route('admin.pages.edit', $page)->with('success', 'Đã tạo trang.'); }
    public function edit(Page $page): View { return view('admin.pages.edit', $this->pageService->formContext($page)); }
    public function update(UpdatePageRequest $request, Page $page): RedirectResponse { $this->pageService->update($page, $request->validated()); return back()->with('success', 'Đã cập nhật trang.'); }
    public function destroy(Page $page): RedirectResponse { $this->pageService->delete($page); return back()->with('success', 'Đã đưa trang vào thùng rác.'); }
}
