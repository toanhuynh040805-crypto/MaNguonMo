@extends('layouts.admin')
@section('title', 'Tổng quan quản trị')
@section('admin-content')
<div class="d-flex justify-content-between flex-wrap gap-3 align-items-center mb-4"><div><span class="eyebrow text-primary">TỔNG QUAN CỬA HÀNG</span><h1 class="section-title mb-0">Bảng điều khiển</h1></div><a class="btn btn-primary" href="{{ route('admin.products.create') }}"><x-ui-icon name="plus" :size="17"/> Thêm sản phẩm</a></div>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3"><div class="stat-box"><span><x-ui-icon name="grid" :size="17"/> Danh mục</span><strong>{{ $categoryCount }}</strong><small>Nhóm sản phẩm</small></div></div>
    <div class="col-md-6 col-xl-3"><div class="stat-box"><span><x-ui-icon name="package" :size="17"/> Sản phẩm</span><strong>{{ $productCount }}</strong><small>Trong hệ thống</small></div></div>
    <div class="col-md-6 col-xl-3"><div class="stat-box"><span><x-ui-icon name="receipt" :size="17"/> Đơn hàng</span><strong>{{ $orderCount }}</strong><small>{{ $pendingCount }} đơn chờ xử lý</small></div></div>
    <div class="col-md-6 col-xl-3"><div class="stat-box"><span><x-ui-icon name="credit-card" :size="17"/> Doanh thu hoàn tất</span><strong class="fs-4">{{ number_format($revenue, 0, ',', '.') }}₫</strong><small>Chỉ tính đơn hoàn tất</small></div></div>
</div>
<div class="alert alert-light border rounded-4 d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
    <div><strong><x-ui-icon name="package" :size="17"/> Theo dõi tồn kho</strong><span class="text-muted ms-2">{{ $lowStockCount }} sản phẩm sắp hết · {{ $outOfStockCount }} sản phẩm đã hết hàng</span></div>
    <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-warning" href="{{ route('admin.products.index', ['stock' => 'low']) }}">Xem sắp hết</a><a class="btn btn-sm btn-outline-danger" href="{{ route('admin.products.index', ['stock' => 'out']) }}">Xem hết hàng</a></div>
</div>
<div class="panel p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-3">
        <div><h2 class="h5 fw-bold mb-1">Doanh thu hoàn tất theo tháng</h2><p class="small text-muted mb-0">6 tháng gần nhất, tính theo thời điểm đơn hàng chuyển sang Hoàn tất.</p></div>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="small text-decoration-none">Xem đơn hoàn tất <x-ui-icon name="arrow-right" :size="16"/></a>
    </div>
    <div class="revenue-chart" role="list" aria-label="Doanh thu trong 6 tháng gần nhất">
        @foreach($monthlyRevenue as $month)
            <div class="revenue-col" role="listitem" title="{{ $month['label'] }}: {{ number_format($month['total'], 0, ',', '.') }} đồng">
                <strong class="small">{{ $month['total'] > 0 ? number_format($month['total'] / 1000000, 1, ',', '.') . 'tr' : '0' }}</strong>
                <div class="revenue-bar-track">
                    <div class="revenue-bar {{ $month['total'] === 0 ? 'revenue-bar-empty' : '' }}" style="height:{{ max(2, round(100 * $month['total'] / $maxMonthlyRevenue)) }}%"></div>
                </div>
                <span class="small text-muted">{{ $month['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>
<div class="panel p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-3">
        <div><h2 class="h5 fw-bold mb-1">Sản phẩm bán chạy</h2><p class="small text-muted mb-0">Top 5 theo số lượng trong các đơn đã hoàn tất · {{ $customerCount }} khách hàng · {{ $reviewCount }} đánh giá</p></div>
    </div>
    @forelse($topProducts as $index => $item)
        <div class="d-flex align-items-center gap-3 py-2 {{ $loop->last ? '' : 'border-bottom' }}">
            <span class="top-product-rank">{{ $index + 1 }}</span>
            <div class="flex-grow-1 fw-semibold">{{ $item->product_name }}</div>
            <div class="text-muted small text-nowrap">{{ (int) $item->sold }} đã bán</div>
            <div class="fw-semibold text-nowrap">{{ number_format((int) $item->revenue, 0, ',', '.') }}₫</div>
        </div>
    @empty
        <p class="text-muted mb-0">Chưa có đơn hoàn tất để thống kê.</p>
    @endforelse
</div>
<div class="panel overflow-hidden"><div class="p-4 d-flex justify-content-between align-items-center"><h2 class="h5 fw-bold mb-0">Đơn hàng gần đây</h2><a href="{{ route('admin.orders.index') }}" class="small">Xem tất cả <x-ui-icon name="arrow-right" :size="16"/></a></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Giá trị</th><th>Trạng thái</th><th></th></tr></thead><tbody>@forelse($recentOrders as $order)
    <tr><td class="fw-semibold">{{ $order->code }}</td><td>{{ $order->user->name }}</td><td>{{ number_format($order->total_amount, 0, ',', '.') }}₫</td><td><x-order-badge :status="$order->status" /></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.orders.show', $order) }}">Xem</a></td></tr>
@empty<tr><td colspan="5" class="py-4 text-center text-muted">Chưa có đơn hàng.</td></tr>@endforelse</tbody></table></div></div>
@endsection
