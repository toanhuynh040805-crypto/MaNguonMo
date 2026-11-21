@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('content')
<div class="container auth-page">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><x-ui-icon name="key" :size="28" :stroke="1.6"/></div>
        <div class="text-center mb-4">
            <span class="eyebrow">KHÔI PHỤC TÀI KHOẢN</span>
            <h1 class="mt-2">Quên mật khẩu?</h1>
            <p class="text-muted small mb-0">Nhập email tài khoản, chúng tôi sẽ gửi liên kết để đặt lại mật khẩu.</p>
        </div>
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4"><label class="form-label" for="email">Địa chỉ email</label><input id="email" name="email" type="email" class="form-control form-control-lg" value="{{ old('email') }}" autocomplete="email" placeholder="ten@example.com" required autofocus></div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Gửi liên kết đặt lại <x-ui-icon name="arrow-right" :size="18"/></button>
        </form>
        <p class="text-center mt-4 mb-0 small text-muted">Đã nhớ mật khẩu? <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Quay lại đăng nhập</a></p>
    </div>
</div>
@endsection
