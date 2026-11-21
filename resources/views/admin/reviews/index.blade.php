@extends('layouts.admin')
@section('title', 'Quản lý đánh giá')
@section('admin-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h1 class="section-title mb-0">Quản lý đánh giá</h1></div>
<form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2 mb-3">
    <div class="col-md-7"><input name="q" class="form-control" maxlength="100" value="{{ request('q') }}" placeholder="Tìm theo sản phẩm hoặc nội dung..." aria-label="Tìm đánh giá"></div>
    <div class="col-md-3"><select name="rating" class="form-select" aria-label="Lọc số sao"><option value="">Mọi số sao</option>@for($star = 5; $star >= 1; $star--)<option value="{{ $star }}" @selected((int) request('rating') === $star)>{{ $star }} sao</option>@endfor</select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Lọc</button></div>
</form>
<div class="panel overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Sản phẩm</th><th>Khách hàng</th><th>Đánh giá</th><th>Ngày</th><th></th></tr></thead>
    <tbody>
    @forelse($reviews as $review)
        <tr>
            <td>@if($review->product)<a href="{{ route('products.show', $review->product->slug) }}" target="_blank" rel="noopener">{{ $review->product->name }}</a>@endif</td>
            <td>{{ $review->user?->name }}<br><span class="small text-muted">{{ $review->user?->email }}</span></td>
            <td style="min-width:240px"><x-star-rating :rating="$review->rating" :size="14" />@if($review->comment)<div class="small mt-1">{{ \Illuminate\Support\Str::limit($review->comment, 160) }}</div>@endif</td>
            <td class="text-nowrap">{{ $review->created_at->format('d/m/Y') }}</td>
            <td>
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Xóa đánh giá này?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="py-5 text-center text-muted">Chưa có đánh giá.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
