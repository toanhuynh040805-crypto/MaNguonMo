@extends('layouts.app')
@section('title', 'Giỏ hàng')
@section('content')
<div class="container page-shell pb-5">
    <div class="page-intro"><div class="eyebrow">MUA SẮM TẠI OPENSHOP</div><h1 class="section-title">Giỏ hàng của bạn</h1><p>Kiểm tra sản phẩm và số lượng trước khi đặt hàng.</p></div>
    @if($items->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><x-ui-icon name="shopping-cart" :size="36" :stroke="1.5"/></div>
            <h2 class="h4 fw-bold">Giỏ hàng đang trống</h2>
            <p class="text-muted">Khám phá thêm các sản phẩm công nghệ trong cửa hàng.</p>
            <a href="{{ route('home') }}" class="btn btn-primary px-4 mt-2"><x-ui-icon name="arrow-left" :size="17"/> Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="panel p-3 p-md-4" aria-labelledby="cart-products-heading">
                    <h2 id="cart-products-heading" class="h5 fw-bold mb-1">Sản phẩm đã chọn</h2>
                    <p class="text-muted small mb-2">Bạn có {{ $items->count() }} loại sản phẩm trong giỏ</p>
                    @foreach($items as $item)
                        <div class="cart-line">
                            <x-product-art :product="$item['product']" class="cart-thumbnail"/>
                            <div class="cart-line-main">
                                <a href="{{ route('products.show', $item['product']->slug) }}" class="cart-line-name">{{ $item['product']->name }}</a>
                                <div class="cart-line-price">{{ number_format($item['product']->price, 0, ',', '.') }}₫</div>
                                <div class="price-note">Giá demo / sản phẩm</div>
                                @if(!$item['product']->is_active || $item['product']->stock < $item['quantity'])
                                    <div class="text-danger small mt-2">Sản phẩm không còn đủ số lượng (kho mẫu: {{ $item['product']->stock }}).</div>
                                @endif
                            </div>
                            <div class="cart-actions">
                                <form method="POST" action="{{ route('cart.update', $item['product']) }}" class="d-flex gap-2 align-items-center">
                                    @csrf @method('PATCH')
                                    <label class="visually-hidden" for="quantity-{{ $item['product']->id }}">Số lượng {{ $item['product']->name }}</label>
                                    <input id="quantity-{{ $item['product']->id }}" class="form-control" type="number" name="quantity" min="1" max="99" value="{{ $item['quantity'] }}" required>
                                    <button class="btn btn-outline-primary btn-sm" type="submit">Cập nhật</button>
                                </form>
                                <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link cart-line-delete btn-sm" type="submit" aria-label="Xóa {{ $item['product']->name }} khỏi giỏ"><x-ui-icon name="trash" :size="18"/></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </section>
            </div>
            <div class="col-lg-4">
                <aside class="panel p-4 checkout-summary" aria-labelledby="cart-summary-heading">
                    <h2 id="cart-summary-heading" class="h5 fw-bold mb-3">Tóm tắt đơn hàng</h2>
                    <div class="summary-row"><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong></div>
                    @if($coupon)
                        <div class="summary-row text-success"><span>Mã <strong class="font-monospace">{{ $coupon->code }}</strong></span><strong>−{{ number_format($discount, 0, ',', '.') }}₫</strong></div>
                    @endif
                    <div class="summary-row"><span>Vận chuyển</span><span>Chưa tính trong demo</span></div>
                    <div class="coupon-box">
                        @if($couponError)<div class="small text-danger mb-2">{{ $couponError }}</div>@endif
                        @if($coupon)
                            <div class="coupon-applied">
                                <span><x-ui-icon name="tag" :size="16"/> <strong class="font-monospace">{{ $coupon->code }}</strong><small class="d-block text-muted">{{ $coupon->summary }}</small></span>
                                <form method="POST" action="{{ route('coupon.remove') }}">@csrf @method('DELETE')<button class="btn btn-link btn-sm text-danger p-0">Gỡ</button></form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('coupon.apply') }}" class="d-flex gap-2">
                                @csrf
                                <label class="visually-hidden" for="coupon-code">Mã giảm giá</label>
                                <input id="coupon-code" name="coupon" class="form-control text-uppercase" maxlength="40" value="{{ old('coupon') }}" placeholder="Nhập mã giảm giá">
                                <button class="btn btn-outline-primary text-nowrap">Áp dụng</button>
                            </form>
                        @endif
                    </div>
                    <div class="summary-row summary-total"><span>Tổng cộng</span><strong>{{ number_format($total, 0, ',', '.') }}₫</strong></div>
                    <p class="text-muted small mb-3">Thanh toán khi nhận hàng (COD). Giá và tồn kho chỉ phục vụ đồ án.</p>
                    @if($canCheckout)
                        <a class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('checkout.create') }}">Tiến hành đặt hàng <x-ui-icon name="arrow-right" :size="19"/></a>
                    @else
                        <button type="button" class="btn btn-secondary btn-lg w-100" disabled>Kiểm tra lại giỏ hàng</button>
                    @endif
                    <a href="{{ route('home') }}" class="btn btn-link text-decoration-none w-100 mt-2"><x-ui-icon name="arrow-left" :size="17"/> Tiếp tục mua sắm</a>
                </aside>
            </div>
        </div>
    @endif
</div>
@endsection
