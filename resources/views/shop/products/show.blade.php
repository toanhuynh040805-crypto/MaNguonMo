@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="container page-shell pb-5">
    <nav class="detail-breadcrumb" aria-label="Đường dẫn">
        <a href="{{ route('home') }}">Trang chủ</a><x-ui-icon name="chevron-right" :size="14"/>
        <a href="{{ route('home', ['category' => $product->category_id]) }}">{{ $product->category?->name }}</a><x-ui-icon name="chevron-right" :size="14"/>
        <span aria-current="page">{{ $product->name }}</span>
    </nav>
    <div class="row g-4 g-lg-5 align-items-start">
        <div class="col-lg-6"><div class="product-detail-image"><x-product-art :product="$product" class="product-art-detail" /></div></div>
        <div class="col-lg-6">
            <span class="eyebrow">{{ $product->category?->name }}</span>
            <h1 class="detail-title mt-2 mb-3">{{ $product->name }}</h1>
            <div class="detail-price">{{ number_format($product->price, 0, ',', '.') }}₫</div>
            <p class="small text-muted mb-3">Giá minh họa phục vụ đồ án, không phải báo giá hiện hành.</p>
            <p class="lh-lg text-muted">{{ $product->description ?: 'Khám phá thiết bị công nghệ phù hợp với nhu cầu học tập, làm việc và giải trí.' }}</p>
            <div class="mt-3 mb-3"><span class="product-stock {{ $product->stock > 0 ? '' : 'out' }}"><x-ui-icon :name="$product->stock > 0 ? 'check-circle' : 'info'" :size="17"/> {{ $product->stock > 0 ? 'Kho mẫu: '.$product->stock.' sản phẩm' : 'Đang tạm hết hàng' }}</span></div>
            @auth
                @if(!auth()->user()->isAdmin() && $product->stock > 0)
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="detail-purchase" data-ajax-cart>
                        @csrf
                        <label class="visually-hidden" for="detail-quantity">Số lượng</label>
                        <input id="detail-quantity" class="form-control form-control-lg" type="number" name="quantity" min="1" max="{{ min($product->stock, 99) }}" value="1" required>
                        <button type="submit" class="btn btn-primary btn-lg"><x-ui-icon name="shopping-cart" :size="21"/> Thêm vào giỏ hàng</button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg mt-3"><x-ui-icon name="user" :size="18"/> Đăng nhập để đặt hàng</a>
            @endauth
            <div class="section-divider"></div>
            <h2 class="h6 fw-bold mb-3">Thông tin mua hàng</h2>
            <div class="detail-highlights">
                <p><x-ui-icon name="credit-card" :size="19"/> Thanh toán COD khi nhận hàng trong bản demo</p>
                <p><x-ui-icon name="receipt" :size="19"/> Xem mã và tình trạng đơn trong tài khoản</p>
                <p><x-ui-icon name="mail" :size="19"/> Email thông báo khi đơn hàng cập nhật trạng thái</p>
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="mt-5" aria-labelledby="related-heading">
            <div class="shop-section-heading"><div><div class="eyebrow">KHÁM PHÁ THÊM</div><h2 id="related-heading" class="section-title">Sản phẩm cùng danh mục</h2></div><a href="{{ route('home', ['category' => $product->category_id]) }}" class="filter-reset">Xem tất cả <x-ui-icon name="arrow-right" :size="16"/></a></div>
            <div class="row g-2 g-md-3">
                @foreach($related as $item)
                    <div class="col-6 col-md-4 col-xl-3"><x-product-card :product="$item" /></div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
