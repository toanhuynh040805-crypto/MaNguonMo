@extends('layouts.admin')
@section('title', $product->exists ? 'Sửa sản phẩm' : 'Thêm sản phẩm')
@section('admin-content')
<a class="text-decoration-none small" href="{{ route('admin.products.index') }}">← Quay lại</a><h1 class="section-title mt-2 mb-4">{{ $product->exists ? 'Sửa sản phẩm' : 'Thêm sản phẩm' }}</h1>
<div class="panel p-4 p-lg-5"><form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">@csrf @if($product->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-8"><label for="name" class="form-label fw-semibold">Tên sản phẩm *</label><input id="name" name="name" class="form-control" maxlength="150" value="{{ old('name', $product->name) }}" required></div>
        <div class="col-md-4"><label for="category_id" class="form-label fw-semibold">Danh mục *</label><select id="category_id" name="category_id" class="form-select" required><option value="">Chọn danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-12"><label for="slug" class="form-label fw-semibold">Đường dẫn (slug)</label><input id="slug" name="slug" class="form-control" maxlength="180" value="{{ old('slug', $product->slug) }}" placeholder="Để trống để tự tạo từ tên sản phẩm"><div class="form-text">Đổi tên sản phẩm không làm đổi đường dẫn. Chỉ sửa ô này khi thật sự cần: đường dẫn cũ sẽ không còn hoạt động và sản phẩm mẫu sẽ mất ảnh chụp gắn theo đường dẫn.</div></div>
        <div class="col-md-6"><label for="price" class="form-label fw-semibold">Đơn giá (VND) *</label><input id="price" type="number" name="price" min="1000" max="999999999" class="form-control" value="{{ old('price', $product->price) }}" required></div>
        <div class="col-md-6"><label for="stock" class="form-label fw-semibold">Tồn kho *</label><input id="stock" type="number" name="stock" min="0" max="1000000" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" required></div>
        <div class="col-12"><label for="description" class="form-label fw-semibold">Mô tả</label><textarea id="description" name="description" rows="4" maxlength="3000" class="form-control">{{ old('description', $product->description) }}</textarea></div>
        <div class="col-md-8"><label for="image" class="form-label fw-semibold">Ảnh sản phẩm (tùy chọn)</label><input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control"><div class="form-text">JPG/PNG/WebP, tối đa 2 MB. Chạy php artisan storage:link để hiện ảnh.</div></div>
        <div class="col-md-4 d-flex align-items-center"><div><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked(old('is_active', $product->is_active))><label for="is_active" class="form-check-label ms-2">Hiển thị để bán</label></div></div>
        @if($product->image_url)
            <div class="col-12">
                <div class="small fw-semibold mb-2">{{ $product->image_path ? 'Ảnh hiện tại (ưu tiên ảnh do Admin tải lên)' : 'Ảnh chụp thật từ thư mục mẫu' }}</div>
                <img src="{{ $product->image_url }}" alt="Ảnh đang hiển thị của {{ $product->name }}" style="height:120px;max-width:240px;object-fit:contain" class="rounded-3 border p-2">
                @if($product->image_path)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                        <label class="form-check-label" for="remove_image">Xóa ảnh đã tải lên, dùng lại ảnh chụp mẫu nếu có</label>
                    </div>
                @endif
                <p class="form-text mb-0">Ảnh mẫu lưu trong public/images/real-products/. Ảnh do Admin tải lên lưu trong storage/app/public/products/.</p>
            </div>
        @endif
    </div>
    <div class="d-flex gap-2 mt-4"><button class="btn btn-primary px-4">{{ $product->exists ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}</button><a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Hủy</a></div>
</form></div>
@endsection
