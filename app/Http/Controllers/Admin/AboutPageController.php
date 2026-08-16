<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AboutPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __construct(private readonly AboutPageService $about) {}
    public function edit(): View { return view('admin.about.edit', ['about' => $this->about->current()]); }
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['hero_title'=>['required','string','max:255'],'hero_intro'=>['nullable','string','max:1000'],'letter_title'=>['nullable','string','max:255'],'letter_content'=>['nullable','string'],'story_title'=>['nullable','string','max:255'],'story_content'=>['nullable','string'],'vision'=>['nullable','string'],'mission'=>['nullable','string'],'core_values_text'=>['nullable','string'],'markets_text'=>['nullable','string'],'commitments_text'=>['nullable','string'],'audiences_text'=>['nullable','string'],'ceo_name'=>['nullable','string','max:255'],'ceo_bio'=>['nullable','string'],'deputy_name'=>['nullable','string','max:255'],'deputy_bio'=>['nullable','string'],'background_image'=>['nullable','string','max:4096'],'hero_image'=>['nullable','string','max:4096'],'seo_title'=>['nullable','string','max:255'],'seo_description'=>['nullable','string']]);
        $data['core_values'] = collect(preg_split('/\R/u', (string) ($data['core_values_text'] ?? '')))->map(fn ($line) => array_map('trim', explode('|', $line, 2)))->filter(fn ($parts) => filled($parts[0] ?? null))->map(fn ($parts) => ['title'=>$parts[0], 'description'=>$parts[1] ?? ''])->values()->all();
        foreach (['markets','commitments','audiences'] as $field) $data[$field] = collect(preg_split('/\R/u', (string) ($data[$field.'_text'] ?? '')))->map(fn ($item)=>trim($item))->filter()->values()->all();
        unset($data['core_values_text'],$data['markets_text'],$data['commitments_text'],$data['audiences_text']); $this->about->update($data);
        return back()->with('success','Đã cập nhật trang Giới thiệu.');
    }
}
