@extends('layouts.admin')
@section('title', 'Mã giảm giá')
@section('admin-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h1 class="section-title mb-0">Mã giảm giá</h1><a class="btn btn-primary" href="{{ route('admin.coupons.create') }}"><x-ui-icon name="plus" :size="17"/> Tạo mã</a></div>
<form method="GET" action="{{ route('admin.coupons.index') }}" class="d-flex gap-2 mb-3"><input name="q" class="form-control" maxlength="40" value="{{ request('q') }}" placeholder="Tìm mã..." aria-label="Tìm mã giảm giá"><button class="btn btn-outline-primary">Tìm kiếm</button></form>
<div class="panel overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Mã</th><th>Ưu đãi</th><th>Đã dùng</th><th>Thời hạn</th><th>Đã giảm</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    @forelse($coupons as $coupon)
        @php
            $state = match (true) {
                ! $coupon->is_active => ['secondary', 'Đã tắt'],
                $coupon->expires_at?->isPast() => ['secondary', 'Hết hạn'],
                $coupon->starts_at?->isFuture() => ['info', 'Chưa bắt đầu'],
                $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit => ['warning', 'Hết lượt'],
                default => ['success', 'Đang chạy'],
            };
        @endphp
        <tr>
            <td><strong class="font-monospace">{{ $coupon->code }}</strong>@if($coupon->description)<br><span class="small text-muted">{{ $coupon->description }}</span>@endif</td>
            <td class="small">{{ $coupon->summary }}</td>
            <td class="text-nowrap">{{ $coupon->used_count }}{{ $coupon->usage_limit !== null ? ' / '.$coupon->usage_limit : '' }}</td>
            <td class="small text-nowrap">{{ $coupon->starts_at?->format('d/m/Y') ?? '—' }} → {{ $coupon->expires_at?->format('d/m/Y') ?? 'không giới hạn' }}</td>
            <td class="text-nowrap">{{ number_format((int) $coupon->discount_total, 0, ',', '.') }}₫</td>
            <td><span class="badge text-bg-{{ $state[0] }}">{{ $state[1] }}</span></td>
            <td><div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.coupons.edit', $coupon) }}">Sửa</a>
                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Xóa mã giảm giá này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </div></td>
        </tr>
    @empty
        <tr><td colspan="7" class="py-5 text-center text-muted">Chưa có mã giảm giá.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-4">{{ $coupons->links() }}</div>
@endsection
