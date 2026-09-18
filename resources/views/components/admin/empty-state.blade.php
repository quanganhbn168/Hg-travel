@props([
    'message' => 'Chưa có dữ liệu.',
    'icon' => null,
])

<tr>
    <td colspan="100" class="text-center py-5 text-body-secondary">
        @if($icon)
            <i class="bi {{ $icon }} d-block fs-3 mb-2" aria-hidden="true"></i>
        @endif
        {{ $message }}
    </td>
</tr>
