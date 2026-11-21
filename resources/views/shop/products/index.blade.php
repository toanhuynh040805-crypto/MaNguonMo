@extends('layouts.app')
@section('title', 'Cửa hàng công nghệ')
@section('content')
<div class="container storefront-home pb-5">
    @if(!request('q') && !request('category'))
    <section class="home-hero-grid" aria-label="Khám phá OpenShop">
        <div class="store-hero">
            <div class="store-hero-copy">
                <span class="eyebrow">KHÁM PHÁ CÔNG NGHỆ</span>
                <h1>Thiết bị phù hợp.<br><em>Trải nghiệm tốt hơn.</em></h1>
                <p>Từ điện thoại đến laptop và phụ kiện, chọn sản phẩm theo nhu cầu của bạn trong một cửa hàng đơn giản, dễ sử dụng.</p>
                <a class="store-hero-cta" href="#danh-sach">Khám phá sản phẩm <x-ui-icon name="arrow-right" :size="18"/></a>
            </div>
            <div class="store-hero-media" aria-hidden="true"><img src="{{ asset('images/real-products/hero-tech.webp') }}" alt="" loading="eager" decoding="async"></div>
        </div>
        <div class="hero-side">
            <a class="promo-tile phone" href="{{ route('home', ['q' => 'iPhone']) }}#danh-sach">
                <span class="promo-copy"><span class="promo-kicker">Gợi ý danh mục</span><strong>Điện thoại<br>thông minh</strong><span class="promo-link">Khám phá <x-ui-icon name="arrow-right" :size="15"/></span></span>
                <img src="{{ asset('images/real-products/iphone-16-pro-128gb.webp') }}" alt="Ảnh chụp tham khảo iPhone 16 Pro" loading="lazy">
            </a>
            <a class="promo-tile laptop" href="{{ route('home', ['q' => 'MacBook']) }}#danh-sach">
                <span class="promo-copy"><span class="promo-kicker">Gợi ý danh mục</span><strong>Laptop cho<br>mọi nhu cầu</strong><span class="promo-link">Xem mẫu <x-ui-icon name="arrow-right" :size="15"/></span></span>
                <img src="{{ asset('images/real-products/macbook-air-13-m4-16gb-256gb.webp') }}" alt="Ảnh chụp tham khảo MacBook Air" loading="lazy">
            </a>
        </div>
    </section>
    <div class="shop-benefits" aria-label="Tiện ích cửa hàng">
        <div class="benefit-item"><x-ui-icon name="grid"/><span><strong>Danh mục rõ ràng</strong>Dễ tìm thiết bị đang cần</span></div>
        <div class="benefit-item"><x-ui-icon name="credit-card"/><span><strong>Đặt hàng COD</strong>Thanh toán khi nhận hàng (demo)</span></div>
        <div class="benefit-item"><x-ui-icon name="mail"/><span><strong>Cập nhật qua email</strong>Theo dõi tiến trình đơn hàng</span></div>
    </div>
    @endif

    @php
        $categoryIcons = [
            'dien-thoai' => 'smartphone',
            'laptop' => 'laptop',
            'may-tinh-bang' => 'tablet',
            'dong-ho-thong-minh' => 'watch',
            'am-thanh' => 'headphones',
            'may-choi-game' => 'gamepad',
            'ban-phim' => 'keyboard',
            'chuot-may-tinh' => 'mouse',
            'phu-kien' => 'cable',
        ];
    @endphp
    <section class="shop-section" id="danh-muc" aria-labelledby="categories-heading">
        <div class="shop-section-heading">
            <div><div class="eyebrow">KHÁM PHÁ NHANH</div><h2 id="categories-heading" class="section-title">Danh mục sản phẩm</h2></div>
            <p class="d-none d-sm-block">Chọn nhóm thiết bị bạn quan tâm</p>
        </div>
        <div class="category-grid">
            @foreach($categories as $cat)
                <a href="{{ route('home', ['category' => $cat->id]) }}#danh-sach" class="category-tile {{ request('category') == $cat->id ? 'selected' : '' }}" @if(request('category') == $cat->id) aria-current="page" @endif>
                    <span class="category-icon"><x-ui-icon :name="$categoryIcons[$cat->slug] ?? 'package'" :size="29" :stroke="1.65"/></span>
                    <span class="category-name">{{ $cat->name }}</span>
                    <span class="category-count">{{ $cat->active_products_count }} sản phẩm</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="shop-section mt-4 mt-md-5" id="danh-sach" aria-labelledby="products-heading">
        <div class="shop-section-heading">
            <div>
                <div class="eyebrow">GỢI Ý TỪ OPENSHOP</div>
                <h2 id="products-heading" class="section-title">{{ request('q') ? 'Kết quả tìm kiếm' : (request('category') ? 'Sản phẩm theo danh mục' : 'Sản phẩm dành cho bạn') }}</h2>
            </div>
            <span class="catalog-results">{{ $products->total() }} sản phẩm · Giá minh họa</span>
        </div>
        <form method="GET" action="{{ route('home') }}#danh-sach" class="catalog-toolbar has-price">
            <div class="catalog-keyword">
                <label for="catalog-q">Tìm sản phẩm</label>
                <input class="form-control" type="search" id="catalog-q" name="q" value="{{ request('q') }}" placeholder="Tên máy, sản phẩm, phụ kiện...">
            </div>
            <div>
                <label for="catalog-category">Danh mục</label>
                <select class="form-select" id="catalog-category" name="category">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="catalog-min-price">Khoảng giá (₫)</label>
                <div class="price-range">
                    <input class="form-control" type="number" id="catalog-min-price" name="min_price" min="0" step="100000" value="{{ request('min_price') }}" placeholder="Từ" aria-label="Giá từ">
                    <input class="form-control" type="number" name="max_price" min="0" step="100000" value="{{ request('max_price') }}" placeholder="Đến" aria-label="Giá đến">
                </div>
            </div>
            <div>
                <label for="catalog-sort">Sắp xếp theo</label>
                <select class="form-select" id="catalog-sort" name="sort">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới nhất</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá từ thấp đến cao</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá từ cao đến thấp</option>
                    <option value="rating" @selected(request('sort') === 'rating')>Đánh giá cao nhất</option>
                </select>
            </div>
            <div class="catalog-submit">
                <button class="btn btn-primary" type="submit"><x-ui-icon name="filter" :size="17"/> Lọc</button>
            </div>
        </form>
        @if(request('category') || request('q') || request('min_price') || request('max_price') || (request('sort') && request('sort') !== 'latest'))
            <div class="mb-3"><a href="{{ route('home') }}#danh-sach" class="filter-reset"><x-ui-icon name="arrow-left" :size="15"/> Xóa bộ lọc, xem tất cả</a></div>
        @endif
        <div class="row g-2 g-md-3">
            @forelse($products as $product)
                <div class="col-6 col-md-4 col-xl-3"><x-product-card :product="$product" :wishlisted="in_array($product->id, $wishlistIds, true)" /></div>
            @empty
                <div class="col-12">
                    <div class="no-results">
                        <x-ui-icon name="search" :size="46" :stroke="1.35"/>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p class="text-muted mb-3">Hãy thử tìm theo tên khác hoặc bỏ bớt bộ lọc.</p>
                        <a href="{{ route('home') }}#danh-sach" class="btn btn-primary">Xem tất cả sản phẩm</a>
                    </div>
                </div>
            @endforelse
        </div>
        @if($products->hasPages())
            <div class="mt-4">{{ $products->links() }}</div>
        @endif
        <p class="catalog-disclaimer"><x-ui-icon name="info" :size="17"/> Các sản phẩm, giá bán và tồn kho phục vụ demo môn học. Ảnh chụp thực tế có thể khác biến thể màu hoặc dung lượng; xem <a href="{{ route('photos.credits') }}">nguồn ảnh</a>.</p>
    </section>
</div>
@endsection
