@props(['product'])
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
    $isDemoPhoto = $product->image_url && str_contains($product->image_url, '/images/real-products/');
@endphp
<div {{ $attributes->class(['product-art']) }}>
    @if($product->image_url)
        <img src="{{ $product->image_url }}" alt="Ảnh chụp tham khảo: {{ $product->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
        @if($isDemoPhoto)
            <span class="photo-ref-label">Ảnh chụp tham khảo</span>
        @endif
    @else
        <span class="product-art-icon"><x-ui-icon :name="$categoryIcons[$product->category?->slug] ?? 'package'" :size="68" :stroke="1.15"/></span>
        <span class="product-art-label">Chưa có hình ảnh</span>
    @endif
</div>
