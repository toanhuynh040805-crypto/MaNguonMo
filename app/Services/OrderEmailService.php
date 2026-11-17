<?php

namespace App\Services;

use App\Mail\OrderUpdateMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderEmailService
{
    /**
     * Called only after the order transaction commits, never while holding
     * stock/order locks. SMTP errors must not undo a placed order.
     * Returns true when Laravel's selected mail transport accepts the message.
     * Note: MAIL_MAILER=log only writes messages to the log, not to Gmail.
     */
    public function send(Order $order, ?string $previousStatus = null): bool
    {
        $order->loadMissing(['user', 'items']);
        $recipient = $order->user?->email;

        if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::warning('OpenShop order email skipped: customer email missing or invalid.', [
                'order_id' => $order->id,
            ]);

            return false;
        }

        try {
            Mail::to($recipient)->send(new OrderUpdateMail($order, $previousStatus));

            return true;
        } catch (Throwable $exception) {
            // Never log SMTP passwords or the full exception (may contain credentials).
            Log::warning('OpenShop order email could not be sent.', [
                'order_id' => $order->id,
                'status' => $order->status,
                'exception_class' => $exception::class,
            ]);

            return false;
        }
    }
}
