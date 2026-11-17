<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="OpenShop - không gian mua sắm công nghệ demo: điện thoại, laptop, máy tính bảng, phụ kiện và quản lý đơn hàng.">
    <title>@yield('title', 'Cửa hàng công nghệ') | OpenShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/openshop.css') }}" rel="stylesheet">
    <link href="{{ asset('css/storefront-refresh.css') }}?v=1" rel="stylesheet">
</head>
<body>
<div class="utility-strip">
    <div class="container utility-inner">
        <span><x-ui-icon name="shield-check" :size="14"/> Website demo đồ án công nghệ</span>
        <span class="d-none d-sm-inline">Giá sản phẩm và tồn kho chỉ mang tính minh họa</span>
    </div>
</div>
<header class="site-header sticky-top">
    <div class="container">
        <div class="header-primary">
            <a class="brand" href="{{ route('home') }}" aria-label="OpenShop - Trang chủ">
                <span class="brand-mark"><x-ui-icon name="package" :size="22" :stroke="2.1"/></span>
                <span class="brand-word">Open<span>Shop</span><i>.</i></span>
            </a>
            <form class="search-header d-none d-lg-flex" method="GET" action="{{ route('home') }}" role="search">
                @if(request()->routeIs('home') && request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <x-ui-icon name="search" :size="20"/>
                <input type="search" name="q" value="{{ request()->routeIs('home') ? request('q', '') : '' }}" placeholder="Tìm điện thoại, laptop, phụ kiện..." aria-label="Tìm sản phẩm OpenShop">
                <button type="submit" aria-label="Tìm kiếm">Tìm kiếm</button>
            </form>
            <div class="header-actions">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a class="header-action d-none d-md-flex" href="{{ route('admin.dashboard') }}"><x-ui-icon name="dashboard"/><span>Quản trị</span></a>
                    @else
                        <a class="header-action d-none d-md-flex" href="{{ route('orders.index') }}"><x-ui-icon name="receipt"/><span>Đơn hàng</span></a>
                        <a class="header-action cart-header-action" href="{{ route('cart.index') }}" aria-label="Giỏ hàng">
                            <span class="header-icon-wrap"><x-ui-icon name="shopping-cart" :size="22"/><span class="cart-count" data-cart-count>{{ array_sum(session('cart', [])) }}</span></span>
                            <span class="d-none d-sm-inline">Giỏ hàng</span>
                        </a>
                    @endif
                    <a class="header-action d-none d-md-flex" href="{{ route('profile.edit') }}"><x-ui-icon name="user"/><span>Tài khoản</span></a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0 d-none d-xl-block">@csrf<button type="submit" class="header-logout" title="Đăng xuất" aria-label="Đăng xuất"><x-ui-icon name="log-out" :size="19"/></button></form>
                @else
                    <a class="header-action" href="{{ route('login') }}"><x-ui-icon name="user"/><span class="d-none d-sm-inline">Đăng nhập</span></a>
                    <a class="header-register d-none d-md-inline-flex" href="{{ route('register') }}">Đăng ký</a>
                @endauth
            </div>
        </div>
        <form class="search-header mobile-search d-flex d-lg-none" method="GET" action="{{ route('home') }}" role="search">
            <x-ui-icon name="search" :size="19"/>
            <input type="search" name="q" value="{{ request()->routeIs('home') ? request('q', '') : '' }}" placeholder="Bạn cần tìm sản phẩm gì?" aria-label="Tìm sản phẩm">
            <button type="submit" aria-label="Tìm kiếm"><x-ui-icon name="arrow-right" :size="19"/></button>
        </form>
    </div>
    <div class="navigation-shell d-none d-lg-block">
        <div class="container">
            <nav class="navigation-list" aria-label="Danh mục nhanh">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') && !request('category') && !request('q') ? 'is-current' : '' }}"><x-ui-icon name="home" :size="17"/> Trang chủ</a>
                @php
                    $mainSlugs = ['dien-thoai', 'laptop', 'may-tinh-bang', 'dong-ho-thong-minh', 'am-thanh', 'phu-kien'];
                    $navCategories = \App\Models\Category::query()->whereIn('slug', $mainSlugs)->get()->sortBy(fn ($item) => array_search($item->slug, $mainSlugs));
                    $navIcons = ['dien-thoai' => 'smartphone', 'laptop' => 'laptop', 'may-tinh-bang' => 'tablet', 'dong-ho-thong-minh' => 'watch', 'am-thanh' => 'headphones', 'phu-kien' => 'cable'];
                @endphp
                @foreach($navCategories as $category)
                    <a href="{{ route('home', ['category' => $category->id]) }}#danh-sach" class="{{ request()->routeIs('home') && request('category') == $category->id ? 'is-current' : '' }}">
                        <x-ui-icon :name="$navIcons[$category->slug] ?? 'package'" :size="17"/> {{ $category->name }}
                    </a>
                @endforeach
                <a href="{{ route('home') }}#danh-muc"><x-ui-icon name="grid" :size="17"/> Xem tất cả</a>
            </nav>
        </div>
    </div>
</header>

<main class="site-main" id="main-content">
    <div class="container site-feedback">
        @if(session('success'))<div role="alert" class="alert alert-success"><x-ui-icon name="check-circle" :size="18"/> <span>{{ session('success') }}</span></div>@endif
        @if(session('error'))<div role="alert" class="alert alert-danger"><x-ui-icon name="info" :size="18"/> <span>{{ session('error') }}</span></div>@endif
        @if($errors->any())
            <div role="alert" class="alert alert-danger"><x-ui-icon name="info" :size="18"/>
                <div><strong>Vui lòng kiểm tra thông tin:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            </div>
        @endif
    </div>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="brand" href="{{ route('home') }}"><span class="brand-mark"><x-ui-icon name="package" :size="20"/></span><span class="brand-word">Open<span>Shop</span><i>.</i></span></a>
                <p>Một không gian trải nghiệm mua sắm thiết bị công nghệ, được xây dựng bằng Laravel và MySQL cho mục đích học tập.</p>
                <span class="demo-notice"><x-ui-icon name="info" :size="16"/> Giá và tồn kho là dữ liệu demo</span>
            </div>
            <div class="footer-links">
                <h3>Khám phá</h3>
                <a href="{{ route('home') }}">Tất cả sản phẩm</a>
                <a href="{{ route('home') }}#danh-muc">Danh mục công nghệ</a>
                <a href="{{ route('photos.credits') }}">Nguồn ảnh sản phẩm</a>
            </div>
            <div class="footer-links">
                <h3>Tài khoản</h3>
                @auth
                    <a href="{{ route('profile.edit') }}">Thông tin tài khoản</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Trang quản trị</a>
                    @else
                        <a href="{{ route('orders.index') }}">Đơn hàng của tôi</a>
                        <a href="{{ route('cart.index') }}">Giỏ hàng</a>
                    @endif
                @else
                    <a href="{{ route('login') }}">Đăng nhập</a>
                    <a href="{{ route('register') }}">Tạo tài khoản</a>
                @endauth
            </div>
            <div class="footer-assurance">
                <h3>Trải nghiệm OpenShop</h3>
                <p><x-ui-icon name="credit-card" :size="18"/> Đặt hàng COD trong bản demo</p>
                <p><x-ui-icon name="mail" :size="18"/> Thông báo trạng thái qua email</p>
                <p><x-ui-icon name="receipt" :size="18"/> Theo dõi và xem lại đơn hàng</p>
            </div>
        </div>
        <div class="footer-bottom"><span>© {{ date('Y') }} OpenShop · Dự án môn Mã nguồn mở</span><span>Giao diện thiết kế riêng · Không liên kết với các chuỗi bán lẻ khác</span></div>
    </div>
</footer>
@unless(request()->routeIs('admin.*'))
<nav class="mobile-bottom-nav d-lg-none" aria-label="Điều hướng nhanh">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><x-ui-icon name="home" :size="22"/><span>Trang chủ</span></a>
    <a href="{{ route('home') }}#danh-muc"><x-ui-icon name="grid" :size="22"/><span>Danh mục</span></a>
    @auth
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}"><x-ui-icon name="dashboard" :size="22"/><span>Quản trị</span></a>
        @else
            <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'active' : '' }}"><x-ui-icon name="shopping-cart" :size="22"/><span>Giỏ hàng</span></a>
            <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}"><x-ui-icon name="receipt" :size="22"/><span>Đơn hàng</span></a>
        @endif
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}"><x-ui-icon name="user" :size="22"/><span>Tài khoản</span></a>
    @else
        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}"><x-ui-icon name="user" :size="22"/><span>Đăng nhập</span></a>
        <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}"><x-ui-icon name="check-circle" :size="22"/><span>Đăng ký</span></a>
    @endauth
</nav>
@endunless
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/shop.js') }}" defer></script>
@stack('scripts')
</body>
</html>
