@extends('layouts.admin')
@section('title', 'Quản lý người dùng')
@section('admin-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h1 class="section-title mb-0">Quản lý người dùng</h1></div>
<form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
    <div class="col-md-6"><input name="q" class="form-control" maxlength="100" value="{{ request('q') }}" placeholder="Tìm tên hoặc email..." aria-label="Tìm người dùng"></div>
    <div class="col-md-2"><select name="role" class="form-select" aria-label="Lọc vai trò"><option value="">Mọi vai trò</option><option value="customer" @selected(request('role') === 'customer')>Khách hàng</option><option value="admin" @selected(request('role') === 'admin')>Quản trị</option></select></div>
    <div class="col-md-2"><select name="state" class="form-select" aria-label="Lọc trạng thái"><option value="">Mọi trạng thái</option><option value="active" @selected(request('state') === 'active')>Đang hoạt động</option><option value="locked" @selected(request('state') === 'locked')>Đã khóa</option></select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Lọc</button></div>
</form>
<div class="panel overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Người dùng</th><th>Vai trò</th><th>Đơn hàng</th><th>Đã mua (hoàn tất)</th><th>Ngày tạo</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    @forelse($users as $user)
        <tr>
            <td><strong>{{ $user->name }}</strong><br><span class="small text-muted">{{ $user->email }}</span></td>
            <td>{{ $user->isAdmin() ? 'Quản trị' : 'Khách hàng' }}</td>
            <td>{{ $user->orders_count }}</td>
            <td class="text-nowrap">{{ number_format((int) $user->completed_total, 0, ',', '.') }}₫</td>
            <td class="text-nowrap">{{ $user->created_at?->format('d/m/Y') }}</td>
            <td>
                @if($user->isLocked())
                    <span class="badge text-bg-danger" title="Khóa lúc {{ $user->locked_at->format('d/m/Y H:i') }}">Đã khóa</span>
                @else
                    <span class="badge text-bg-success">Hoạt động</span>
                @endif
            </td>
            <td>
                @unless($user->isAdmin())
                    <form method="POST" action="{{ route('admin.users.lock', $user) }}" onsubmit="return confirm('{{ $user->isLocked() ? 'Mở khóa' : 'Khóa' }} tài khoản này?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm {{ $user->isLocked() ? 'btn-outline-success' : 'btn-outline-danger' }}">{{ $user->isLocked() ? 'Mở khóa' : 'Khóa' }}</button>
                    </form>
                @endunless
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="py-5 text-center text-muted">Không tìm thấy người dùng.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
