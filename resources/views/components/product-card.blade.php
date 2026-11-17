@props(['product'])
<article class="card product-card h-100">
    <a href="{{ route('products.show', $product->slug) }}" class="product-photo-link text-decoration-none" aria-label="Xem {{ $product->name }}">
        <x-product-art :product="$product" />
    </a>
    <div class="card-body d-flex flex-column">
        <div class="product-category">{{ $product->category?->name ?? 'Thiết bị' }}</div>
        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none"><h3 class="product-name">{{ $product->name }}</h3></a>
        <div class="card-bottom">
            <div>
                <div class="product-price">{{ number_format($product->price, 0, ',', '.') }}₫</div>
                <div class="price-note">Giá demo</div>
                <div class="product-stock {{ $product->stock <= 0 ? 'out' : '' }}">{{ $product->stock > 0 ? 'Kho mẫu: '.$product->stock : 'Tạm hết hàng' }}</div>
            </div>
            @auth
                @if(!auth()->user()->isAdmin() && $product->stock > 0)
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="m-0" data-ajax-cart>
                        @csrf <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary btn-add" title="Thêm vào giỏ hàng" aria-label="Thêm {{ $product->name }} vào giỏ"><x-ui-icon name="plus" :size="21"/></button>
                    </form>
                @endif
            @else
                @if($product->stock > 0)
                    <a class="btn btn-outline-primary product-action-link" href="{{ route('login') }}" title="Đăng nhập để mua" aria-label="Đăng nhập để mua {{ $product->name }}"><x-ui-icon name="plus" :size="20"/></a>
                @endif
            @endauth
        </div>
    </div>
</article>
