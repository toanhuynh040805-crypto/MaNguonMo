@extends('layouts.app')
@section('title', 'Đơn hàng của tôi')
@section('content')
<div class="container page-shell pb-5">
    <div class="page-intro"><div class="eyebrow">TÀI KHOẢN KHÁCH HÀNG</div><h1 class="section-title">Đơn hàng của tôi</h1><p>Theo dõi quá trình xử lý và xem lại các đơn hàng bạn đã đặt.</p></div>
    <form method="GET" action="{{ route('orders.index') }}" class="panel p-3 p-md-4 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label" for="my_order_q">Mã đơn hoặc tên sản phẩm</label><input id="my_order_q" name="q" class="form-control" maxlength="100" value="{{ request('q') }}" placeholder="Ví dụ: DH000001, iPhone"></div>
            <div class="col-md-4"><label class="form-label" for="my_order_status">Trạng thái đơn</label><select id="my_order_status" name="status" class="form-select"><option value="">Tất cả trạng thái</option>@foreach(\App\Models\Order::STATUSES as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary"><x-ui-icon name="search" :size="17"/> Tìm đơn</button></div>
            <div class="col-md-1"><a class="filter-reset" href="{{ route('orders.index') }}">Xóa lọc</a></div>
        </div>
    </form>
    <div class="order-card-list">
        @forelse($orders as $order)
            <article class="order-list-card">
                <div class="order-card-top">
                    <div><a class="order-card-code" href="{{ route('orders.show', $order) }}">{{ $order->code }}</a><div class="order-card-date">Đặt ngày {{ $order->created_at->format('d/m/Y \l\ú\c H:i') }}</div></div>
                    <x-order-badge :status="$order->status"/>
                </div>
                <div class="order-card-bottom">
                    <div><div class="text-muted small mb-1">Giá trị đơn hàng (demo)</div><div class="order-card-amount">{{ number_format($order->total_amount, 0, ',', '.') }}₫</div></div>
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Xem chi tiết <x-ui-icon name="arrow-right" :size="16"/></a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-icon"><x-ui-icon name="receipt" :size="34"/></div>
                <h2 class="h5 fw-bold">Chưa tìm thấy đơn hàng</h2>
                <p class="text-muted">Bạn chưa có đơn phù hợp với bộ lọc này.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Khám phá sản phẩm</a>
            </div>
        @endforelse
    </div>
    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
