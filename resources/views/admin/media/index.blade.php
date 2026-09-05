@extends('layouts.admin')

@section('title', 'Thư viện media')
@section('page-title', 'Thư viện media')

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <x-card type="primary" title="Tải tệp mới">
                <form id="media-upload-form" class="row g-2" enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-9"><input class="form-control" name="file" type="file" accept="{{ $acceptedFiles }}" required></div>
                    <div class="col-md-3"><button class="btn btn-primary w-100">Tải lên thư viện</button></div>
                </form>
            </x-card>
        </div>
        <div class="col-12">
            <form method="get" class="d-flex gap-2 mb-3">
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm tên tệp">
                <button class="btn btn-outline-primary">Tìm</button>
                <label class="d-flex align-items-center gap-2 text-nowrap"><input type="checkbox" name="archived" value="1" @checked(request()->boolean('archived'))> Đã lưu trữ</label>
            </form>
            <x-admin.table-card title="Tệp đã tải lên">
                <div class="row g-3">
                    @forelse ($media as $item)
                        <div class="col-sm-6 col-lg-3">
                            <a class="card text-decoration-none" href="{{ $item['original_url'] }}" target="_blank" rel="noopener">
                                @if (str_starts_with((string) $item['mime_type'], 'image/'))
                                    <img class="card-img-top object-fit-cover" style="height: 160px" src="{{ $item['thumbnail_url'] ?: $item['original_url'] }}" alt="{{ $item['name'] }}">
                                @else
                                    <div class="card-body d-flex align-items-center justify-content-center" style="height: 160px"><i class="bi bi-file-earmark fs-1"></i></div>
                                @endif
                                <div class="card-body py-2"><div class="text-truncate fw-semibold" title="{{ $item['name'] }}">{{ $item['name'] }}</div><small class="text-muted">{{ number_format($item['size'] / 1024, 0) }} KB</small>
                                    @if($item['state'] === 'pending')<small class="d-block text-warning-emphasis">Chờ gắn vào nội dung · tự dọn sau {{ config('media.pending_hours') }} giờ nếu chưa sử dụng</small>@endif
                                </div>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-media-usage="{{ route('admin.media.usage', $item['id']) }}">Nơi sử dụng</button>
                            @if(auth('admin')->user()?->hasPermissionTo('media.delete', 'web'))
                                @if($item['state'] === 'archived')
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-media-restore="{{ route('admin.media.restore', $item['id']) }}">Khôi phục</button>
                                @else
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" data-media-archive="{{ route('admin.media.destroy', $item['id']) }}">Lưu trữ</button>
                                @endif
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Chưa có tệp nào trong thư viện.</p>
                    @endforelse
                </div>
                <x-slot:footer>{{ $media->links() }}</x-slot:footer>
            </x-admin.table-card>
        </div>
    </div>
@endsection

@push('js')
<script>
document.getElementById('media-upload-form').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget; HgMedia.busy(form, 1);
    try { await HgMedia.request(@json(route('admin.media.upload.library')), { method: 'POST', body: new FormData(form) }); window.location.reload(); }
    catch (error) { Swal.fire('Không thể tải tệp', error.message, 'error'); }
    finally { HgMedia.busy(form, -1); }
});
document.querySelectorAll('[data-media-usage]').forEach(button => button.onclick = async () => {
    try { const data = await HgMedia.request(button.dataset.mediaUsage); Swal.fire('Nơi sử dụng', data.usages.join('\n') || 'Chưa gắn vào nội dung.', 'info'); }
    catch (error) { Swal.fire('Không thể kiểm tra', error.message, 'error'); }
});
document.querySelectorAll('[data-media-restore]').forEach(button => button.onclick = async () => {
    try { await HgMedia.request(button.dataset.mediaRestore, { method: 'PUT' }); window.location.reload(); }
    catch (error) { Swal.fire('Không thể khôi phục', error.message, 'error'); }
});
document.querySelectorAll('[data-media-archive]').forEach(button => button.onclick = async () => {
    const confirmed = await Swal.fire({ title: 'Lưu trữ tệp?', text: 'Tệp sẽ ẩn khỏi thư viện. File gốc được giữ để khôi phục.', showCancelButton: true, confirmButtonText: 'Lưu trữ', cancelButtonText: 'Hủy' });
    if (!confirmed.isConfirmed) return;
    try { await HgMedia.request(button.dataset.mediaArchive, { method: 'DELETE' }); window.location.reload(); }
    catch (error) { Swal.fire('Chưa thể lưu trữ', error.message, 'error'); }
});
</script>
@endpush
