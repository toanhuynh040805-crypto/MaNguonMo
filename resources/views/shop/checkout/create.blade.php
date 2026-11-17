@extends('layouts.app')
@section('title', 'Đặt hàng')
@section('content')
<div class="container page-shell pb-5">
    <div class="page-intro"><div class="eyebrow">HOÀN TẤT ĐẶT HÀNG</div><h1 class="section-title">Thông tin giao hàng</h1><p>Kiểm tra người nhận và đơn hàng trước khi xác nhận.</p></div>
    <form method="POST" action="{{ route('checkout.store') }}" class="row g-4">
        @csrf
        <div class="col-lg-7">
            <section class="panel p-4 p-lg-5" aria-labelledby="shipping-form-title">
                <h2 id="shipping-form-title" class="h5 fw-bold mb-4"><span class="checkout-step"><x-ui-icon name="map-pin" :size="18"/></span> Người nhận hàng</h2>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label" for="recipient_name">Họ và tên người nhận *</label><input id="recipient_name" class="form-control form-control-lg" name="recipient_name" maxlength="120" value="{{ old('recipient_name', auth()->user()->name) }}" autocomplete="name" required></div>
                    <div class="col-12"><label class="form-label" for="phone">Số điện thoại *</label><input id="phone" class="form-control form-control-lg" name="phone" type="tel" inputmode="tel" maxlength="11" value="{{ old('phone') }}" placeholder="09xxxxxxxx" autocomplete="tel" required></div>
                    <div class="col-12"><label class="form-label" for="address">Địa chỉ giao hàng *</label><textarea id="address" class="form-control" name="address" rows="3" maxlength="500" autocomplete="street-address" placeholder="Số nhà, tên đường, phường/xã, tỉnh/thành phố" required>{{ old('address') }}</textarea></div>
                    <div class="col-12"><label class="form-label" for="note">Ghi chú (tùy chọn)</label><textarea id="note" class="form-control" name="note" rows="3" maxlength="1000" placeholder="Lưu ý thêm cho đơn hàng">{{ old('note') }}</textarea></div>
                </div>
                <div class="section-divider"></div>
                <h2 class="h5 fw-bold mb-3"><span class="checkout-step"><x-ui-icon name="credit-card" :size="18"/></span> Phương thức thanh toán</h2>
                <div class="payment-note"><x-ui-icon name="check-circle" :size="22"/><div><strong>Thanh toán khi nhận hàng (COD)</strong><div class="small text-muted mt-1">Website demo không nhận thanh toán trực tuyến.</div></div></div>
                <div class="payment-note mt-3"><x-ui-icon name="mail" :size="22"/><div><strong>Thông báo qua email</strong><div class="small text-muted mt-1">Thông tin xác nhận và cập nhật đơn hàng sẽ gửi tới <strong>{{ auth()->user()->email }}</strong>. <a href="{{ route('profile.edit') }}">Đổi email tài khoản</a> nếu cần.</div></div></div>
            </section>
        </div>
        <div class="col-lg-5">
            <aside class="panel p-4 checkout-summary" aria-labelledby="checkout-summary-title">
                <h2 id="checkout-summary-title" class="h5 fw-bold mb-3">Đơn hàng của bạn</h2>
                @foreach($items as $item)
                    <div class="order-line"><div><strong>{{ $item['product']->name }}</strong><small>Số lượng: {{ $item['quantity'] }}</small></div><strong class="text-nowrap">{{ number_format($item['subtotal'], 0, ',', '.') }}₫</strong></div>
                @endforeach
                <div class="summary-row summary-total mt-3"><span>Tổng thanh toán</span><strong>{{ number_format($total, 0, ',', '.') }}₫</strong></div>
                <button type="submit" class="btn btn-primary btn-lg w-100 d-flex justify-content-center align-items-center gap-2 mt-3">Xác nhận đặt hàng <x-ui-icon name="arrow-right" :size="19"/></button>
                <p class="text-muted small text-center mb-0 mt-3">Hệ thống sẽ kiểm tra lại tồn kho khi tạo đơn.</p>
            </aside>
        </div>
    </form>
</div>
@endsection
