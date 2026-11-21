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
            <h1 class="detail-title mt-2 mb-2">{{ $product->name }}</h1>
            <div class="mb-3">
                @if($product->reviews_count > 0)
                    <a href="#danh-gia" class="text-decoration-none"><x-star-rating :rating="$product->reviews_avg_rating" :count="$product->reviews_count" /></a>
                @else
                    <span class="small text-muted">Chưa có đánh giá</span>
                @endif
            </div>
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
                <x-wishlist-button :product="$product" :saved="in_array($product->id, $wishlistIds, true)" :label="true" class="mt-3" />
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

    <section class="panel p-4 p-lg-5 mt-5" id="danh-gia" aria-labelledby="reviews-heading">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div><div class="eyebrow">TỪ NGƯỜI ĐÃ MUA</div><h2 id="reviews-heading" class="section-title mb-0">Đánh giá sản phẩm</h2></div>
            @if($product->reviews_count > 0)
                <div class="review-summary">
                    <span class="review-score">{{ number_format($product->reviews_avg_rating, 1, ',', '.') }}</span>
                    <div><x-star-rating :rating="$product->reviews_avg_rating" :size="18" /><div class="small text-muted">{{ $product->reviews_count }} lượt đánh giá</div></div>
                </div>
            @endif
        </div>

        @if($canReview)
            <form method="POST" action="{{ route('reviews.store', $product) }}" class="border rounded-4 p-3 p-md-4 mb-4">
                @csrf
                <h3 class="h6 fw-bold mb-2">{{ $myReview ? 'Sửa đánh giá của bạn' : 'Viết đánh giá của bạn' }}</h3>
                <fieldset class="mb-3">
                    <legend class="form-label small mb-1">Số sao *</legend>
                    <div class="rating-picker">
                        @for($star = 5; $star >= 1; $star--)
                            <input type="radio" name="rating" id="rating-{{ $star }}" value="{{ $star }}" @checked((int) old('rating', $myReview?->rating) === $star) required>
                            <label for="rating-{{ $star }}" title="{{ $star }} sao"><x-ui-icon name="star" :size="30" :stroke="1.5"/><span class="visually-hidden">{{ $star }} sao</span></label>
                        @endfor
                    </div>
                </fieldset>
                <label class="form-label small" for="review-comment">Nhận xét (tùy chọn)</label>
                <textarea id="review-comment" name="comment" rows="3" maxlength="1000" class="form-control mb-3" placeholder="Chia sẻ trải nghiệm của bạn với sản phẩm">{{ old('comment', $myReview?->comment) }}</textarea>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $myReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}</button>
                </div>
            </form>
            @if($myReview)
                <form method="POST" action="{{ route('reviews.destroy', $product) }}" class="mb-4 mt-n3" onsubmit="return confirm('Xóa đánh giá của bạn?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-link text-danger p-0 small">Xóa đánh giá của tôi</button>
                </form>
            @endif
        @elseif(auth()->check() && !auth()->user()->isAdmin())
            <p class="small text-muted mb-4"><x-ui-icon name="info" :size="16"/> Bạn có thể đánh giá sau khi nhận hàng thành công (đơn ở trạng thái Hoàn tất).</p>
        @elseif(!auth()->check())
            <p class="small text-muted mb-4"><a href="{{ route('login') }}">Đăng nhập</a> để đánh giá sản phẩm bạn đã mua.</p>
        @endif

        @forelse($reviews as $review)
            <article class="review-item">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div><strong>{{ $review->user?->name ?? 'Khách hàng' }}</strong> <span class="badge text-bg-light border ms-1">Đã mua hàng</span></div>
                    <time class="small text-muted" datetime="{{ $review->updated_at->toIso8601String() }}">{{ $review->updated_at->format('d/m/Y') }}</time>
                </div>
                <x-star-rating :rating="$review->rating" :size="14" />
                @if($review->comment)<p>{{ $review->comment }}</p>@endif
            </article>
        @empty
            <p class="text-muted mb-0">Chưa có đánh giá nào cho sản phẩm này.</p>
        @endforelse
        @if($reviews->hasPages())
            <div class="mt-3">{{ $reviews->fragment('danh-gia')->links() }}</div>
        @endif
    </section>

    @if($related->isNotEmpty())
        <section class="mt-5" aria-labelledby="related-heading">
            <div class="shop-section-heading"><div><div class="eyebrow">KHÁM PHÁ THÊM</div><h2 id="related-heading" class="section-title">Sản phẩm cùng danh mục</h2></div><a href="{{ route('home', ['category' => $product->category_id]) }}" class="filter-reset">Xem tất cả <x-ui-icon name="arrow-right" :size="16"/></a></div>
            <div class="row g-2 g-md-3">
                @foreach($related as $item)
                    <div class="col-6 col-md-4 col-xl-3"><x-product-card :product="$item" :wishlisted="in_array($item->id, $wishlistIds, true)" /></div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
