<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // completed_at tracks actual completion instead of order creation date.
        $since = now()->startOfMonth()->subMonths(5);
        $completedByMonth = Order::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $since)
            ->get(['completed_at', 'total_amount'])
            ->groupBy(fn (Order $order) => $order->completed_at->format('Y-m'))
            ->map(fn ($orders) => $orders->sum('total_amount'));

        $monthlyRevenue = collect(range(5, 0))->map(function (int $offset) use ($completedByMonth): array {
            $month = now()->startOfMonth()->subMonths($offset);
            return [
                'label' => $month->format('m/Y'),
                'total' => (int) ($completedByMonth[$month->format('Y-m')] ?? 0),
            ];
        });
        $maxMonthlyRevenue = max(1, $monthlyRevenue->max('total'));

        return view('admin.dashboard', [
            'monthlyRevenue' => $monthlyRevenue,
            'maxMonthlyRevenue' => $maxMonthlyRevenue,
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'orderCount' => Order::count(),
            'pendingCount' => Order::where('status', 'pending')->count(),
            'lowStockCount' => Product::whereBetween('stock', [1, 5])->count(),
            'outOfStockCount' => Product::where('stock', 0)->count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'recentOrders' => Order::with('user')->latest()->limit(6)->get(),
            // Best sellers from completed orders; product_name keeps history for deleted products.
            'topProducts' => OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'completed')
                ->groupBy('order_items.product_id', 'order_items.product_name')
                ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) as sold, SUM(order_items.subtotal) as revenue')
                ->orderByDesc('sold')->orderByDesc('revenue')
                ->limit(5)->get(),
            'reviewCount' => Review::count(),
            'customerCount' => User::where('role', 'customer')->count(),
        ]);
    }
}
