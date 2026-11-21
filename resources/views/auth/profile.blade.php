@extends('layouts.app')
@section('title', 'Tài khoản của tôi')
@section('content')
<div class="container page-shell pb-5">
    <div class="page-intro">
        <div class="account-hero">
            <div class="account-avatar"><x-ui-icon name="user" :size="29"/></div>
            <div><div class="eyebrow">THÔNG TIN CÁ NHÂN</div><h1 class="section-title mb-1">Tài khoản của tôi</h1><p class="account-info mb-0">Cập nhật hồ sơ và bảo mật đăng nhập.</p></div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <section class="panel p-4 p-lg-5 h-100" aria-labelledby="profile-info-title">
                <h2 id="profile-info-title" class="h5 fw-bold mb-2">Thông tin tài khoản</h2>
                <p class="text-muted small mb-4">Email này cũng nhận thông báo khi đơn hàng thay đổi trạng thái.</p>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-3"><label class="form-label" for="profile_name">Họ và tên</label><input class="form-control form-control-lg" type="text" id="profile_name" name="name" value="{{ old('name', $user->name) }}" maxlength="120" required autocomplete="name"></div>
                    <div class="mb-3"><label class="form-label" for="profile_email">Địa chỉ email</label><input class="form-control form-control-lg" type="email" id="profile_email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="email"></div>
                    <div class="mb-3"><label class="form-label" for="profile_current_password">Mật khẩu hiện tại <span class="text-muted fw-normal">(bắt buộc khi đổi email)</span></label><input class="form-control form-control-lg" type="password" id="profile_current_password" name="current_password" autocomplete="current-password"></div>
                    <p class="small text-muted mb-4">Vai trò: <strong>{{ $user->isAdmin() ? 'Quản trị viên' : 'Khách hàng' }}</strong></p>
                    <button type="submit" class="btn btn-primary px-4">Lưu thay đổi</button>
                </form>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="panel p-4 p-lg-5 h-100" aria-labelledby="profile-password-title">
                <h2 id="profile-password-title" class="h5 fw-bold mb-2">Bảo mật tài khoản</h2>
                <p class="text-muted small mb-4">Đổi mật khẩu nếu bạn cần cập nhật thông tin đăng nhập.</p>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-3"><label class="form-label" for="current_password">Mật khẩu hiện tại</label><input class="form-control form-control-lg" type="password" id="current_password" name="current_password" autocomplete="current-password" required></div>
                    <div class="mb-3"><label class="form-label" for="new_password">Mật khẩu mới (ít nhất 8 ký tự)</label><input class="form-control form-control-lg" type="password" id="new_password" name="password" minlength="8" autocomplete="new-password" required></div>
                    <div class="mb-4"><label class="form-label" for="password_confirmation">Nhập lại mật khẩu mới</label><input class="form-control form-control-lg" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
                    <button type="submit" class="btn btn-outline-primary px-4">Cập nhật mật khẩu</button>
                </form>
            </section>
        </div>
    </div>
    <div class="d-xl-none mt-4">
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn btn-outline-secondary"><x-ui-icon name="log-out" :size="17"/> Đăng xuất khỏi OpenShop</button>
        </form>
    </div>
</div>
@endsection
