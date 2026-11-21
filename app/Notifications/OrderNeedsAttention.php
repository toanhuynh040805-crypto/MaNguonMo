<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

/** In-site notification for Admins: a new order to confirm, or a customer cancellation. */
class OrderNeedsAttention extends Notification
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
        $amount = number_format($this->order->total_amount, 0, ',', '.') . '₫';
        $isNew = $this->previousStatus === null;

        return [
            'order_id' => $this->order->id,
            'order_code' => $code,
            'status' => $this->order->status,
            'title' => $isNew ? 'Đơn hàng mới' : 'Khách đã hủy đơn',
            'message' => $isNew
                ? "{$this->order->recipient_name} vừa đặt đơn {$code} ({$amount}) cần xác nhận."
                : "{$this->order->recipient_name} đã hủy đơn {$code}; tồn kho đã được hoàn lại.",
            'url' => route('admin.orders.show', $this->order, false),
        ];
    }
}
