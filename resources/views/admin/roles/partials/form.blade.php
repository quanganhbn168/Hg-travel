@php
    $selected = collect(old('permissions', $selectedPermissions ?? []));
    $isSystemRole = $role->exists && $role->name === 'admin';
@endphp

<div class="row g-3">
    <div class="col-12">
        <x-card type="primary" title="Thông tin vai trò">
            <div class="mb-3">
                <label class="form-label" for="role-name">Tên vai trò <span class="text-danger">*</span></label>
                <input id="role-name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $role->name) }}" required @readonly($isSystemRole)>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($isSystemRole)
                    <div class="form-text">Đây là vai trò hệ thống và luôn giữ toàn bộ quyền.</div>
                @else
                    <div class="form-text">Tên dùng để nhận diện nhóm tài khoản trong trang quản trị.</div>
                @endif
            </div>
        </x-card>
    </div>

    <div class="col-12">
        <x-card type="info" title="Quyền truy cập">
            <p class="text-muted small mb-3">Mỗi vai trò luôn được giữ quyền <code>admin.access</code> để đăng nhập khu vực quản trị. Chọn thêm các quyền nghiệp vụ cần thiết.</p>
            <div class="row g-3">
                @foreach($groups as $group)
                    <div class="col-xl-4 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold mb-3">{{ $group['label'] }}</h6>
                            @foreach($group['permissions'] as $permission)
                                <label class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($selected->contains($permission->name)) @disabled($isSystemRole)>
                                    <span class="form-check-label">{{ \App\Services\RoleService::actionLabel($permission->name, $actionLabels) }}</span>
                                    <small class="d-block text-muted ms-4">{{ $permission->name }}</small>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions')<div class="text-danger small mt-3">{{ $message }}</div>@enderror
            @error('permissions.*')<div class="text-danger small mt-3">{{ $message }}</div>@enderror
        </x-card>
    </div>

    <div class="col-12 text-end">
        <a class="btn btn-outline-secondary" href="{{ route('admin.roles.index') }}">Hủy</a>
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    </div>
</div>
