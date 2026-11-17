<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Thông báo đơn hàng {{ $order->code }}</title>
</head>
<body style="background:#f5f7fc;margin:0;padding:24px 12px;color:#18223b;font-family:Arial,Helvetica,sans-serif">
  <div style="max-width:660px;background:#fff;border-radius:16px;overflow:hidden;margin:auto;border:1px solid #e8ecf6">
    <div style="background:#1e214b;color:white;padding:26px 30px">
      <div style="font-weight:800;letter-spacing:-.5px;font-size:25px">OpenShop.</div>
      <div style="font-size:13px;margin-top:5px;color:#e3e2ff">Thông báo tự động về đơn hàng</div>
    </div>
    <div style="padding:28px 30px">
      <p>Xin chào <strong>{{ $order->user?->name ?? $order->recipient_name }}</strong>,</p>
      @if($previousStatus === null)
        <h1 style="font-size:22px;color:#4b3ad3;margin:12px 0">Đặt hàng thành công!</h1>
        <p>OpenShop đã ghi nhận đơn hàng của bạn. Đơn đang chờ xác nhận.</p>
      @else
        <h1 style="font-size:22px;color:#4b3ad3;margin:12px 0">Cập nhật trạng thái đơn hàng</h1>
        <p>Đơn hàng của bạn đã chuyển từ
          <strong>{{ \App\Models\Order::STATUSES[$previousStatus] ?? $previousStatus }}</strong>
          sang <strong>{{ $order->status_label }}</strong>.
        </p>
        @if($order->status === 'shipping')
          <p>Đơn hàng đang trong quá trình giao. Bạn có thể theo dõi trạng thái trong tài khoản OpenShop.</p>
        @elseif($order->status === 'completed')
          <p>Đơn hàng đã hoàn tất. Cảm ơn bạn đã sử dụng OpenShop!</p>
        @elseif($order->status === 'cancelled')
          <p>Đơn hàng đã được hủy. Nếu cần hỗ trợ, bạn có thể liên hệ email của cửa hàng.</p>
        @endif
      @endif
      <div style="background:#f4f3ff;border-radius:12px;padding:16px 20px;margin:24px 0">
        <div style="font-size:12px;color:#6c7188">MÃ ĐƠN HÀNG</div>
        <strong style="font-size:21px">{{ $order->code }}</strong>
        <p style="margin:8px 0 0;font-size:13px">Trạng thái: {{ $order->status_label }}</p>
        <p style="margin:6px 0 0;font-size:13px">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
      </div>
      <h2 style="font-size:17px">Sản phẩm trong đơn</h2>
      <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead><tr style="background:#f5f7fc;text-align:left"><th style="padding:10px">Sản phẩm</th><th style="padding:10px;text-align:center">SL</th><th style="padding:10px;text-align:right">Thành tiền</th></tr></thead>
        <tbody>
        @foreach($order->items as $item)
          <tr>
            <td style="padding:12px 10px;border-bottom:1px solid #e9ecf5">{{ $item->product_name }}</td>
            <td style="padding:12px 10px;text-align:center;border-bottom:1px solid #e9ecf5">{{ $item->quantity }}</td>
            <td style="padding:12px 10px;text-align:right;border-bottom:1px solid #e9ecf5">{{ number_format($item->subtotal, 0, ',', '.') }} ₫</td>
          </tr>
        @endforeach
        </tbody>
      </table>
      <p style="font-size:18px;font-weight:bold;text-align:right">Tổng đơn: {{ number_format($order->total_amount, 0, ',', '.') }} ₫</p>
      <h2 style="font-size:17px;margin-top:24px">Thông tin nhận hàng</h2>
      <p style="font-size:14px;line-height:1.8">
        {{ $order->recipient_name }}<br>
        Điện thoại: {{ $order->phone }}<br>
        Địa chỉ: {{ $order->address }}<br>
        Thanh toán: COD (khi nhận hàng)
      </p>
      <p style="font-size:13px;color:#6e748d;margin-top:22px">Đây là thông báo tự động từ website OpenShop phục vụ đồ án. Đơn giá và tồn kho trong hệ thống là dữ liệu demo. Vui lòng không chuyển khoản dựa trên email này.</p>
    </div>
    <div style="background:#f6f7fc;padding:18px 30px;font-size:12px;color:#707995">
      OpenShop · Website bán hàng demo · Email này được gửi đến địa chỉ tài khoản đã đặt hàng.
    </div>
  </div>
</body>
</html>
