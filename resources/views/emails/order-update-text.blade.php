OpenShop - Thông báo đơn hàng {{ $order->code }}

Xin chào {{ $order->user?->name ?? $order->recipient_name }},

@if($previousStatus === null)
Đơn hàng của bạn đã được ghi nhận thành công và đang chờ xác nhận.
@else
Đơn hàng đã chuyển từ {{ \App\Models\Order::STATUSES[$previousStatus] ?? $previousStatus }} sang {{ $order->status_label }}.
@endif

Mã đơn: {{ $order->code }}
Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}
Trạng thái hiện tại: {{ $order->status_label }}

Sản phẩm:
@foreach($order->items as $item)
- {{ $item->product_name }} x {{ $item->quantity }}: {{ number_format($item->subtotal, 0, ',', '.') }} VND
@endforeach

@if($order->discount_amount > 0)
Tạm tính: {{ number_format($order->subtotal_amount, 0, ',', '.') }} VND
Mã giảm giá {{ $order->coupon_code }}: -{{ number_format($order->discount_amount, 0, ',', '.') }} VND
@endif
Tổng đơn: {{ number_format($order->total_amount, 0, ',', '.') }} VND

Người nhận: {{ $order->recipient_name }}
Số điện thoại: {{ $order->phone }}
Địa chỉ: {{ $order->address }}
Thanh toán: COD - khi nhận hàng.

OpenShop - Website demo môn Lập trình mã nguồn mở.
Đây là thông báo tự động. Vui lòng không chuyển tiền theo email này.
