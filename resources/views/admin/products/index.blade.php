@extends('layouts.admin')
@section('title', 'Quản lý sản phẩm')
@section('admin-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h1 class="section-title mb-0">Quản lý sản phẩm</h1><a class="btn btn-primary" href="{{ route('admin.products.create') }}"><x-ui-icon name="plus" :size="17"/> Thêm sản phẩm</a></div>
<form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 mb-3"><div class="col-md-6"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Tìm tên sản phẩm..." aria-label="Tìm sản phẩm"></div><div class="col-md-3"><select name="category" class="form-select" aria-label="Lọc danh mục"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></div><div class="col-md-2"><select name="stock" class="form-select" aria-label="Lọc tồn kho"><option value="">Mọi mức tồn</option><option value="low" @selected(request('stock') === 'low')>Sắp hết (1–5)</option><option value="out" @selected(request('stock') === 'out')>Hết hàng</option></select></div><div class="col-md-1 d-grid"><button class="btn btn-outline-primary">Lọc</button></div></form>
<div class="panel overflow-hidden"><div class="table-responsive admin-products-scroll" role="region" aria-label="Bảng sản phẩm có thể cuộn ngang" tabindex="0"><table class="table table-hover align-middle mb-0 admin-products-table"><thead><tr><th>#</th><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Kho</th><th>Hiển thị</th><th>Thao tác</th></tr></thead><tbody>
@forelse($products as $product)
<tr>
    <td class="text-muted admin-col-id">{{ $product->id }}</td>
    <td class="admin-col-product">
        <div class="admin-product-cell">
            <x-product-art :product="$product" class="admin-thumbnail" />
            <div class="admin-product-meta">
                <a class="admin-product-name" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                <span class="admin-product-slug">{{ $product->slug }}</span>
            </div>
        </div>
    </td>
    <td class="admin-col-category">{{ $product->category?->name }}</td>
    <td class="admin-col-price text-nowrap">{{ number_format($product->price, 0, ',', '.') }}₫</td>
    <td class="admin-col-stock">{{ $product->stock }}</td>
    <td class="admin-col-status"><span class="badge text-bg-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Đang bán' : 'Đã ẩn' }}</span></td>
    <td class="admin-col-actions">
        <div class="admin-product-actions">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
            </form>
        </div>
    </td>
</tr>
@empty<tr><td colspan="7" class="py-5 text-center text-muted">Không tìm thấy sản phẩm.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-4">{{ $products->links() }}</div>
@endsection
