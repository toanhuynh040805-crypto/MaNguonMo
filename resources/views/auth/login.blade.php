@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<div class="container auth-page">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><x-ui-icon name="lock" :size="28" :stroke="1.6"/></div>
        <div class="text-center mb-4">
            <span class="eyebrow">TÀI KHOẢN OPENSHOP</span>
            <h1 class="mt-2">Chào mừng trở lại</h1>
            <p class="text-muted small mb-0">Đăng nhập để tiếp tục mua sắm và theo dõi đơn hàng.</p>
        </div>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="email">Địa chỉ email</label><input id="email" name="email" type="email" class="form-control form-control-lg" value="{{ old('email') }}" autocomplete="email" placeholder="ten@example.com" required autofocus></div>
            <div class="mb-3"><label class="form-label" for="password">Mật khẩu</label><input id="password" name="password" type="password" class="form-control form-control-lg" autocomplete="current-password" placeholder="Nhập mật khẩu của bạn" required></div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check m-0"><input class="form-check-input" type="checkbox" value="1" name="remember" id="remember"><label class="form-check-label small" for="remember">Ghi nhớ đăng nhập</label></div>
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Đăng nhập <x-ui-icon name="arrow-right" :size="18"/></button>
        </form>
        <p class="text-center mt-4 mb-0 small text-muted">Chưa có tài khoản? <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Tạo tài khoản mới</a></p>
    </div>
</div>
@endsection
