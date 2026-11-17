<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderUpdateMail extends Mailable
{
    public function __construct(
        public Order $order,
        public ?string $previousStatus = null,
    ) {
        $this->order->loadMissing(['user', 'items']);
    }

    public function envelope(): Envelope
    {
        $action = $this->previousStatus === null
            ? 'Đặt hàng thành công'
            : 'Cập nhật đơn hàng: ' . $this->order->status_label;

        return new Envelope(subject: "OpenShop | {$action} - {$this->order->code}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-update',
            text: 'emails.order-update-text',
        );
    }
}
