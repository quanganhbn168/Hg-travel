<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmTourScheduleImportRequest;
use App\Http\Requests\Admin\ImportTourPackageRequest;
use App\Http\Requests\Admin\PrepareTourScheduleImportRequest;
use App\Models\Tour;
use App\Services\HgTripImportService;
use App\Services\TourScheduleSheetImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class TourImportController extends Controller
{
    public function create(): View
    {
        return view('admin.tours.import');
    }

    public function importPackage(ImportTourPackageRequest $request, HgTripImportService $importer): RedirectResponse
    {
        try {
            $file = $request->file('package_file');
            $result = $importer->import((string) $file?->getRealPath(), false, true);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['package_file' => $this->message($exception)]);
        }

        return redirect()->route('admin.tours.index')->with(
            'success',
            "Đã nhập gói ZIP: {$result['tours']} tour, {$result['schedules']} lịch khởi hành và {$result['images']} ảnh.",
        );
    }

    public function prepareSchedules(PrepareTourScheduleImportRequest $request, TourScheduleSheetImportService $importer): View|RedirectResponse
    {
        $temporaryPath = null;

        try {
            $path = $request->hasFile('schedule_file')
                ? (string) $request->file('schedule_file')?->getRealPath()
                : ($temporaryPath = $this->downloadGoogleSheet((string) $request->input('google_sheet_url')));
            $preview = $importer->preview($path, (int) $request->integer('import_year'));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['schedule_file' => $this->message($exception)]);
        } finally {
            if ($temporaryPath !== null) {
                File::delete($temporaryPath);
            }
        }

        $tours = Tour::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->values();
        $byNormalisedName = $tours->keyBy(fn (Tour $tour): string => $this->normaliseName($tour->name));
        $suggestions = [];

        foreach ($preview['sources'] as $sourceKey => $source) {
            $suggestions[$sourceKey] = $byNormalisedName
                ->get($this->normaliseName((string) $source['name']))
                ?->id;
        }

        session(['tour_schedule_import' => $preview]);

        return view('admin.tours.import-mapping', compact('preview', 'tours', 'suggestions'));
    }

    public function confirmSchedules(ConfirmTourScheduleImportRequest $request, TourScheduleSheetImportService $importer): RedirectResponse
    {
        $preview = session('tour_schedule_import');

        if (! is_array($preview)) {
            return redirect()->route('admin.tours.import.create')
                ->withErrors(['schedule_file' => 'Phiên đối chiếu đã hết hạn. Hãy tải lại file để kiểm tra lại.']);
        }

        try {
            $result = $importer->import($preview, $request->validated('mappings', []));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['mappings' => $this->message($exception)]);
        }

        session()->forget('tour_schedule_import');

        return redirect()->route('admin.tours.index')->with(
            'success',
            "Đã cập nhật lịch cho {$result['tours']} tour: thêm {$result['created']} ngày, cập nhật {$result['updated']} ngày".($result['skipped'] ? ", bỏ qua {$result['skipped']} tour." : '.'),
        );
    }

    private function downloadGoogleSheet(string $url): string
    {
        if (preg_match('#^https://docs\\.google\\.com/spreadsheets/d/([A-Za-z0-9_-]+)#', trim($url), $matches) !== 1) {
            throw new RuntimeException('Link Google Sheet chưa đúng định dạng. Hãy dùng link docs.google.com/spreadsheets/d/...');
        }

        $response = Http::timeout(45)
            ->accept('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->get('https://docs.google.com/spreadsheets/d/'.$matches[1].'/export?format=xlsx');

        if (! $response->successful() || ! str_starts_with($response->body(), 'PK')) {
            throw new RuntimeException('Không tải được Google Sheet. Hãy đặt quyền xem bằng liên kết hoặc tải file Excel (.xlsx) lên trực tiếp.');
        }

        if (strlen($response->body()) > 50 * 1024 * 1024) {
            throw new RuntimeException('Google Sheet xuất ra vượt quá giới hạn 50 MB. Hãy tách file trước khi nhập.');
        }

        $directory = storage_path('app/.tour-schedule-import');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.Str::uuid().'.xlsx';
        File::put($path, $response->body());

        return $path;
    }

    private function normaliseName(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', Str::upper(Str::ascii($value))));
    }

    private function message(Throwable $exception): string
    {
        return $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'Không thể xử lý file nhập. Hãy kiểm tra lại định dạng và thử lại.';
    }
}
