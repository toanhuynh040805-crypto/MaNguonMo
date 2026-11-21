<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Queued so checkout and Admin actions never wait for SMTP.
 *
 * SerializesModels is intentionally NOT used: the whole order (with items) is
 * serialized as a snapshot, so a worker running late still describes the status
 * change that triggered this email rather than the order's latest status.
 */
class OrderUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable;

    /** Retry transient SMTP failures (Gmail throttling, timeouts). */
    public int $tries = 3;

    /** Seconds to wait before each retry. */
    public array $backoff = [30, 120];

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
