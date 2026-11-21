@extends('layouts.app')
@section('title', 'Sản phẩm yêu thích')
@section('content')
<div class="container page-shell pb-5">
    <div class="page-intro"><div class="eyebrow">TÀI KHOẢN KHÁCH HÀNG</div><h1 class="section-title">Sản phẩm yêu thích</h1><p>Lưu lại thiết bị bạn quan tâm để xem và mua sau.</p></div>
    @if($products->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><x-ui-icon name="heart" :size="34" :stroke="1.5"/></div>
            <h2 class="h5 fw-bold">Chưa có sản phẩm yêu thích</h2>
            <p class="text-muted">Nhấn biểu tượng trái tim trên sản phẩm để lưu vào đây.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Khám phá sản phẩm</a>
        </div>
    @else
        <p class="text-muted small">{{ $products->total() }} sản phẩm đã lưu</p>
        <div class="row g-2 g-md-3">
            @foreach($products as $product)
                <div class="col-6 col-md-4 col-xl-3"><x-product-card :product="$product" :wishlisted="true" /></div>
            @endforeach
        </div>
        @if($products->hasPages())
            <div class="mt-4">{{ $products->links() }}</div>
        @endif
    @endif
</div>
@endsection
