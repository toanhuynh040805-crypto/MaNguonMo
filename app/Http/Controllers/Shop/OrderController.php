<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Ownership checks are done by OrderPolicy through the routes' "can" middleware. */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Order::statusRule()],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        // Begin with the user ID restriction so search can never leak other users' orders.
        $orders = $request->user()->orders()
            ->withCount('items')
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $term = trim($filters['q']);
                if (preg_match('/^(?:DH0*)?([0-9]+)$/i', $term, $matches)) {
                    $query->where('orders.id', (int) $matches[1]);
                } else {
                    // Free text: match a product name in the order or the recipient.
                    $query->where(fn (Builder $sub) => $sub
                        ->where('recipient_name', 'like', '%' . $term . '%')
                        ->orWhereHas('items', fn (Builder $items) => $items->where('product_name', 'like', '%' . $term . '%')));
                }
            })
            ->latest()->paginate(10)->withQueryString();

        return view('shop.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'statusHistories']);
        return view('shop.orders.show', compact('order'));
    }

    public function receipt(Order $order): View
    {
        $order->load('items');
        return view('orders.receipt', compact('order'));
    }

    public function cancel(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        // Customers may only cancel while the order is still pending.
        $previous = $orders->transition($order, 'cancelled', $request->user(), allowedFrom: ['pending']);

        if ($previous === null) {
            return redirect()->route('orders.show', $order)->with('error', 'Chỉ hủy được đơn đang chờ xác nhận.');
        }

        // Notify only once, after the cancellation and stock restore commit.
        $emailAccepted = $orders->notify($order, $previous, $request->user());

        return redirect()->route('orders.show', $order)->with(
            'success',
            'Đã hủy đơn hàng và hoàn lại tồn kho.' .
                ($emailAccepted ? ' Đã xử lý thông báo email.' : ' Chưa gửi được email hủy đơn.')
        );
    }
}
