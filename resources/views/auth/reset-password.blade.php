@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('content')
<div class="container auth-page">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><x-ui-icon name="lock" :size="28" :stroke="1.6"/></div>
        <div class="text-center mb-4">
            <span class="eyebrow">KHÔI PHỤC TÀI KHOẢN</span>
            <h1 class="mt-2">Đặt mật khẩu mới</h1>
            <p class="text-muted small mb-0">Mật khẩu mới cần ít nhất 8 ký tự.</p>
        </div>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3"><label class="form-label" for="email">Địa chỉ email</label><input id="email" name="email" type="email" class="form-control form-control-lg" value="{{ old('email', $email) }}" autocomplete="email" required></div>
            <div class="mb-3"><label class="form-label" for="password">Mật khẩu mới</label><input id="password" name="password" type="password" class="form-control form-control-lg" minlength="8" autocomplete="new-password" required autofocus></div>
            <div class="mb-4"><label class="form-label" for="password_confirmation">Nhập lại mật khẩu mới</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control form-control-lg" minlength="8" autocomplete="new-password" required></div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Đặt lại mật khẩu</button>
        </form>
    </div>
</div>
@endsection
