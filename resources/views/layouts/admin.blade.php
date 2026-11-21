@extends('layouts.app')
@section('content')
<div class="container admin-workspace pb-5">
    <div class="admin-workspace-title d-lg-none"><span class="eyebrow text-primary">QUẢN TRỊ OPENSHOP</span><h1 class="section-title mb-3">Bảng quản lý</h1></div>
    <div class="row g-4">
        <aside class="col-lg-3 col-xl-2">
            <nav class="admin-sidebar p-3" aria-label="Điều hướng quản trị">
                <div class="sidebar-heading">QUẢN LÝ CỬA HÀNG</div>
                <a class="admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><x-ui-icon name="dashboard" :size="18"/> Tổng quan</a>
                <a class="admin-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><x-ui-icon name="grid" :size="18"/> Danh mục</a>
                <a class="admin-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><x-ui-icon name="package" :size="18"/> Sản phẩm</a>
                <a class="admin-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><x-ui-icon name="receipt" :size="18"/> Đơn hàng</a>
                <a class="admin-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}"><x-ui-icon name="tag" :size="18"/> Mã giảm giá</a>
                <a class="admin-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><x-ui-icon name="users" :size="18"/> Người dùng</a>
                <a class="admin-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}"><x-ui-icon name="star" :size="18"/> Đánh giá</a>
                <div class="sidebar-divider"></div>
                <a class="admin-link" href="{{ route('home') }}"><x-ui-icon name="arrow-left" :size="18"/> Xem cửa hàng</a>
            </nav>
        </aside>
        <div class="col-lg-9 col-xl-10">@yield('admin-content')</div>
    </div>
</div>
@endsection
