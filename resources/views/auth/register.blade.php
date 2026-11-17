@extends('layouts.app')
@section('title', 'Tạo tài khoản')
@section('content')
<div class="container auth-page">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><x-ui-icon name="user" :size="29" :stroke="1.6"/></div>
        <div class="text-center mb-4">
            <span class="eyebrow">THAM GIA OPENSHOP</span>
            <h1 class="mt-2">Tạo tài khoản</h1>
            <p class="text-muted small mb-0">Tạo tài khoản để đặt hàng và xem lịch sử mua sắm.</p>
        </div>
        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="name">Họ và tên</label><input id="name" name="name" type="text" class="form-control form-control-lg" value="{{ old('name') }}" maxlength="120" autocomplete="name" placeholder="Họ tên của bạn" required></div>
            <div class="mb-3"><label class="form-label" for="email">Địa chỉ email</label><input id="email" name="email" type="email" class="form-control form-control-lg" value="{{ old('email') }}" autocomplete="email" placeholder="ten@example.com" required></div>
            <div class="mb-3"><label class="form-label" for="password">Mật khẩu (ít nhất 8 ký tự)</label><input id="password" name="password" type="password" class="form-control form-control-lg" minlength="8" autocomplete="new-password" required></div>
            <div class="mb-4"><label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control form-control-lg" minlength="8" autocomplete="new-password" required></div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Đăng ký <x-ui-icon name="arrow-right" :size="18"/></button>
        </form>
        <p class="text-center mt-4 mb-0 small text-muted">Đã có tài khoản? <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Đăng nhập</a></p>
    </div>
</div>
@endsection
