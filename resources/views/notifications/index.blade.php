@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.app')
@section('title', 'Thông báo')
@section(auth()->user()->isAdmin() ? 'admin-content' : 'content')
<div class="{{ auth()->user()->isAdmin() ? '' : 'container page-shell pb-5' }}">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
        <div><div class="eyebrow">TRUNG TÂM THÔNG BÁO</div><h1 class="section-title mb-0">Thông báo</h1></div>
        @if(auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-sm btn-outline-primary"><x-ui-icon name="check-circle" :size="16"/> Đánh dấu tất cả đã đọc</button></form>
        @endif
    </div>
    <div class="panel overflow-hidden">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.open', $notification->id) }}" @class(['notification-item', 'is-unread' => $notification->unread()])>
                <span class="notification-icon"><x-ui-icon :name="($notification->data['status'] ?? '') === 'cancelled' ? 'info' : 'receipt'" :size="19"/></span>
                <span class="flex-grow-1">
                    <strong>{{ $notification->data['title'] ?? 'Thông báo' }}</strong>
                    <span class="d-block text-muted small">{{ $notification->data['message'] ?? '' }}</span>
                </span>
                <time class="small text-muted text-nowrap" datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('d/m/Y H:i') }}">{{ $notification->created_at->diffForHumans() }}</time>
            </a>
        @empty
            <div class="empty-state border-0">
                <div class="empty-icon"><x-ui-icon name="bell" :size="34" :stroke="1.5"/></div>
                <h2 class="h5 fw-bold">Chưa có thông báo</h2>
                <p class="text-muted mb-0">Cập nhật về đơn hàng sẽ xuất hiện tại đây.</p>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())<div class="mt-4">{{ $notifications->links() }}</div>@endif
</div>
@endsection
