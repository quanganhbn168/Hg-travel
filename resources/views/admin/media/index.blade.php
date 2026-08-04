@extends('layouts.admin')

@section('title', 'Thư viện media')
@section('page-title', 'Thư viện media')

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <x-card type="primary" title="Tải tệp mới">
                <form id="media-upload-form" class="row g-2" enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-9"><input class="form-control" name="file" type="file" accept="image/*,.pdf,.doc,.docx" required></div>
                    <div class="col-md-3"><button class="btn btn-primary w-100">Tải lên thư viện</button></div>
                </form>
            </x-card>
        </div>
        <div class="col-12">
            <x-admin.table-card title="Tệp đã tải lên">
                <div class="row g-3">
                    @forelse ($media as $item)
                        <div class="col-sm-6 col-lg-3">
                            <a class="card h-100 text-decoration-none" href="{{ $item->getUrl() }}" target="_blank">
                                @if (str_starts_with((string) $item->mime_type, 'image/'))
                                    <img class="card-img-top object-fit-cover" style="height: 160px" src="{{ $item->getUrl() }}" alt="{{ $item->name }}">
                                @else
                                    <div class="card-body d-flex align-items-center justify-content-center" style="height: 160px"><i class="bi bi-file-earmark fs-1"></i></div>
                                @endif
                                <div class="card-body py-2"><div class="text-truncate fw-semibold">{{ $item->file_name }}</div><small class="text-muted">{{ number_format($item->size / 1024, 0) }} KB</small></div>
                            </a>
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
document.getElementById('media-upload-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const response = await fetch(@json(route('admin.media.upload.temp')), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: new FormData(event.currentTarget) });
    if (!response.ok) { Swal.fire('Không thể tải tệp', 'Vui lòng kiểm tra định dạng và dung lượng.', 'error'); return; }
    window.location.reload();
});
</script>
@endpush
