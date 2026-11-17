@extends('layouts.app')
@section('title', 'Cửa hàng')
@section('content')
<section class="container pb-3">
    <div class="hero d-flex align-items-center mb-4 mb-md-5">
        <div class="hero-content p-4 p-md-5">
            <span class="eyebrow">OPENSHOP · CÔNG NGHỆ CHO MỌI NGƯỜI</span>
            <h1 class="display-5 fw-bold lh-sm mt-3 mb-3">Tìm thiết bị phù hợp.<br><span class="hero-gradient">Khám phá mỗi ngày.</span></h1>
            <p class="hero-subtitle mb-4">Điện thoại, laptop, tablet, đồng hồ thông minh và phụ kiện trong một không gian mua sắm hiện đại.</p>
            <a href="#danh-sach" class="btn btn-light rounded-pill fw-bold px-4 py-3">Khám phá sản phẩm ↗</a>
        </div>
        <div class="hero-visual" aria-hidden="true"><div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div><img src="{{ asset('images/real-products/hero-tech.webp') }}" class="hero-product-image" alt="" loading="eager" decoding="async"></div>
    </div>
</section>
<section class="container pb-4" aria-label="Danh mục sản phẩm">
    @php
        $categoryIcons = [
            'dien-thoai' => '📱',
            'laptop' => '💻',
            'may-tinh-bang' => '▤',
            'dong-ho-thong-minh' => '⌚',
            'am-thanh' => '🎧',
            'may-choi-game' => '🎮',
            'ban-phim' => '⌨️',
            'chuot-may-tinh' => '🖱️',
            'phu-kien' => '🔌',
        ];
    @endphp
    <div class="d-flex align-items-end justify-content-between gap-2 mb-3">
        <div><div class="eyebrow text-primary">KHÁM PHÁ DANH MỤC</div><h2 class="section-title mb-0">Bạn đang tìm gì?</h2></div>
        <span class="small text-muted">Giá và số lượng chỉ dùng cho demo</span>
    </div>
    <div class="category-grid">
        @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->id]) }}#danh-sach" class="category-tile {{ (int)request('category') === $cat->id ? 'selected' : '' }}">
                <span class="category-icon" aria-hidden="true">{{ $categoryIcons[$cat->slug] ?? '📦' }}</span>
                <span class="category-name">{{ $cat->name }}</span>
                <span class="category-count">{{ $cat->active_products_count }} mẫu</span>
            </a>
        @endforeach
    </div>
</section>
<section class="container pb-5" id="danh-sach">
    <div class="d-flex justify-content-between flex-wrap gap-2 align-items-end mb-3">
        <div><div class="eyebrow text-primary">DANH MỤC OPENSHOP</div><h2 class="section-title mb-0">Sản phẩm công nghệ</h2></div>
        <span class="small text-muted">{{ $products->total() }} sản phẩm · Giá demo</span>
    </div>
    <form method="GET" action="{{ route('home') }}" class="row g-2 filter-bar mb-4 align-items-center">
        <div class="col-md-5"><label class="visually-hidden" for="q">Tìm kiếm</label><input id="q" class="form-control form-control-lg" name="q" value="{{ request('q') }}" placeholder="🔍  Tìm tên sản phẩm..."></div>
        <div class="col-md-3"><label class="visually-hidden" for="category">Danh mục</label><select id="category" class="form-select form-select-lg" name="category"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="visually-hidden" for="sort">Sắp xếp</label><select id="sort" class="form-select form-select-lg" name="sort"><option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới nhất</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-primary btn-lg">Lọc sản phẩm</button></div>
    </form>
    <div class="row g-3 g-md-4">
        @forelse($products as $product)
            <div class="col-6 col-md-4 col-xl-3"><x-product-card :product="$product" /></div>
        @empty
            <div class="col-12"><div class="empty-state"><div class="display-5 mb-2">🔎</div><h3 class="h5">Không tìm thấy sản phẩm</h3><p class="text-muted">Hãy thử từ khóa hoặc danh mục khác.</p><a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill">Xóa bộ lọc</a></div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</section>
@endsection
