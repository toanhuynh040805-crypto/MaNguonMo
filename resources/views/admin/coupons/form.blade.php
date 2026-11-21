@extends('layouts.admin')
@section('title', $coupon->exists ? 'Sửa mã giảm giá' : 'Tạo mã giảm giá')
@section('admin-content')
<a class="text-decoration-none small" href="{{ route('admin.coupons.index') }}">← Quay lại</a>
<h1 class="section-title mt-2 mb-4">{{ $coupon->exists ? 'Sửa mã '.$coupon->code : 'Tạo mã giảm giá' }}</h1>
<div class="panel p-4 p-lg-5"><form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
    @csrf @if($coupon->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label for="code" class="form-label fw-semibold">Mã *</label><input id="code" name="code" class="form-control text-uppercase font-monospace" maxlength="40" value="{{ old('code', $coupon->code) }}" placeholder="VD: OPENSHOP10" required></div>
        <div class="col-md-8"><label for="description" class="form-label fw-semibold">Mô tả</label><input id="description" name="description" class="form-control" maxlength="255" value="{{ old('description', $coupon->description) }}" placeholder="VD: Ưu đãi khai trương"></div>
        <div class="col-md-4"><label for="type" class="form-label fw-semibold">Loại giảm *</label><select id="type" name="type" class="form-select" required>@foreach(\App\Models\Coupon::TYPES as $value => $label)<option value="{{ $value }}" @selected(old('type', $coupon->type) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-4"><label for="value" class="form-label fw-semibold">Mức giảm * <span class="fw-normal text-muted">(% hoặc ₫)</span></label><input id="value" name="value" type="number" min="1" class="form-control" value="{{ old('value', $coupon->value) }}" required></div>
        <div class="col-md-4"><label for="max_discount" class="form-label fw-semibold">Giảm tối đa (₫)</label><input id="max_discount" name="max_discount" type="number" min="1000" class="form-control" value="{{ old('max_discount', $coupon->max_discount) }}" placeholder="Chỉ cho loại %"></div>
        <div class="col-md-4"><label for="min_order_amount" class="form-label fw-semibold">Đơn tối thiểu (₫)</label><input id="min_order_amount" name="min_order_amount" type="number" min="0" class="form-control" value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}"></div>
        <div class="col-md-4"><label for="usage_limit" class="form-label fw-semibold">Tổng số lượt dùng</label><input id="usage_limit" name="usage_limit" type="number" min="1" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Để trống = không giới hạn"></div>
        <div class="col-md-4 d-flex align-items-end"><div class="form-check mb-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked(old('is_active', $coupon->is_active))><label for="is_active" class="form-check-label">Đang áp dụng</label></div></div>
        <div class="col-md-6"><label for="starts_at" class="form-label fw-semibold">Bắt đầu</label><input id="starts_at" name="starts_at" type="datetime-local" class="form-control" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"></div>
        <div class="col-md-6"><label for="expires_at" class="form-label fw-semibold">Hết hạn</label><input id="expires_at" name="expires_at" type="datetime-local" class="form-control" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"></div>
    </div>
    @if($coupon->exists)<p class="small text-muted mt-3 mb-0">Đã dùng {{ $coupon->used_count }} lượt. Đơn bị hủy sẽ trả lại lượt dùng.</p>@endif
    <div class="d-flex gap-2 mt-4"><button class="btn btn-primary px-4">{{ $coupon->exists ? 'Lưu thay đổi' : 'Tạo mã' }}</button><a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Hủy</a></div>
</form></div>
@endsection
