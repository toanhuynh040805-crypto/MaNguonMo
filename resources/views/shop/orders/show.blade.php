@extends('layouts.app')
@section('title', 'Đơn hàng '.$order->code)
@section('content')
<div class="container page-shell pb-5">
    <nav class="detail-breadcrumb" aria-label="Đường dẫn"><a href="{{ route('home') }}">Trang chủ</a><x-ui-icon name="chevron-right" :size="14"/><a href="{{ route('orders.index') }}">Đơn hàng</a><x-ui-icon name="chevron-right" :size="14"/><span aria-current="page">{{ $order->code }}</span></nav>
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap page-intro">
        <div><div class="eyebrow">THEO DÕI ĐƠN HÀNG</div><h1 class="section-title">Đơn hàng {{ $order->code }}</h1><p>Đặt ngày {{ $order->created_at->format('d/m/Y H:i') }}</p></div>
        <x-order-badge :status="$order->status"/>
    </div>
    <div class="d-flex justify-content-end mb-3"><a href="{{ route('orders.receipt', $order) }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><x-ui-icon name="receipt" :size="17"/> Xem phiếu đặt hàng</a></div>
    <div class="order-detail-grid">
        <section class="panel p-4" aria-labelledby="ordered-products-title">
            <h2 id="ordered-products-title" class="h5 fw-bold mb-2">Sản phẩm đã đặt</h2>
            @foreach($order->items as $item)
                <div class="order-line">
                    <div><strong>{{ $item->product_name }}</strong><small>{{ number_format($item->price, 0, ',', '.') }}₫ × {{ $item->quantity }}</small></div>
                    <strong class="text-nowrap">{{ number_format($item->subtotal, 0, ',', '.') }}₫</strong>
                </div>
            @endforeach
            <div class="summary-row summary-total"><span>Tổng cộng</span><strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong></div>
            <p class="price-note mb-0">Giá demo · Thanh toán khi nhận hàng (COD)</p>
        </section>
        <aside class="panel p-4 order-meta" aria-labelledby="shipping-info-title">
            <h2 id="shipping-info-title" class="h5 fw-bold mb-3">Thông tin giao hàng</h2>
            <dl>
                <dt>Người nhận</dt><dd>{{ $order->recipient_name }}</dd>
                <dt>Điện thoại</dt><dd>{{ $order->phone }}</dd>
                <dt>Địa chỉ</dt><dd>{{ $order->address }}</dd>
                <dt>Ghi chú</dt><dd>{{ $order->note ?: 'Không có' }}</dd>
                <dt>Thanh toán</dt><dd>COD</dd>
            </dl>
            @if($order->status === 'pending')
                <div class="section-divider"></div>
                <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn đang chờ xác nhận?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger w-100">Hủy đơn hàng</button>
                </form>
            @endif
        </aside>
    </div>
    <section class="panel p-4 mt-4" aria-labelledby="history-heading">
        <h2 id="history-heading" class="h5 fw-bold mb-4">Lịch sử xử lý</h2>
        <x-order-timeline :histories="$order->statusHistories"/>
    </section>
</div>
@endsection
