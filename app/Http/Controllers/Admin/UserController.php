<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:admin,customer'],
            'state' => ['nullable', 'in:active,locked'],
        ]);

        $users = User::query()
            ->withCount('orders')
            ->withSum(['orders as completed_total' => fn (Builder $q) => $q->where('status', 'completed')], 'total_amount')
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $sub) => $sub
                ->where('name', 'like', '%' . $term . '%')->orWhere('email', 'like', '%' . $term . '%')))
            ->when($filters['role'] ?? null, fn (Builder $q, string $role) => $q->where('role', $role))
            ->when(($filters['state'] ?? null) === 'locked', fn (Builder $q) => $q->whereNotNull('locked_at'))
            ->when(($filters['state'] ?? null) === 'active', fn (Builder $q) => $q->whereNull('locked_at'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /** Lock or unlock a customer account. Admin accounts cannot be locked here. */
    public function toggleLock(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin() || $user->is($request->user())) {
            return back()->with('error', 'Không thể khóa tài khoản quản trị.');
        }

        $user->forceFill(['locked_at' => $user->isLocked() ? null : now()])->save();

        return back()->with('success', $user->isLocked()
            ? "Đã khóa tài khoản {$user->email}. Người dùng sẽ bị đăng xuất ở lần truy cập kế tiếp."
            : "Đã mở khóa tài khoản {$user->email}.");
    }
}
