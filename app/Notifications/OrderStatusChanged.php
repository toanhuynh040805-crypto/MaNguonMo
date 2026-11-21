<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

/** In-site notification for the customer who owns the order. */
class OrderStatusChanged extends Notification
{
    public function __construct(private Order $order, private ?string $previousStatus = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $code = $this->order->code;
        $message = match (true) {
            $this->previousStatus === null => "Đơn {$code} đã được đặt thành công và đang chờ xác nhận.",
            $this->order->status === 'confirmed' => "Đơn {$code} đã được xác nhận và đang chuẩn bị hàng.",
            $this->order->status === 'shipping' => "Đơn {$code} đang được giao đến bạn.",
            $this->order->status === 'completed' => "Đơn {$code} đã hoàn tất. Hãy đánh giá sản phẩm bạn đã mua nhé!",
            $this->order->status === 'cancelled' => "Đơn {$code} đã bị hủy.",
            default => "Đơn {$code} đã chuyển sang {$this->order->status_label}.",
        };

        return [
            'order_id' => $this->order->id,
            'order_code' => $code,
            'status' => $this->order->status,
            'title' => $this->previousStatus === null ? 'Đặt hàng thành công' : 'Cập nhật đơn hàng',
            'message' => $message,
            'url' => route('orders.show', $this->order, false),
        ];
    }
}
