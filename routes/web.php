<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\CouponController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\Shop\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::view('/nguon-anh', 'shop.photo-credits')->name('photos.credits');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');

    // Password reset (names follow Laravel's convention used by the ResetPassword notification).
    Route::get('/quen-mat-khau', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/quen-mat-khau', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/tai-khoan/mat-khau', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/thong-bao', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/thong-bao/da-doc', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/thong-bao/{id}', [NotificationController::class, 'open'])->name('notifications.open');
});

Route::middleware(['auth', 'role:customer'])->group(function (): void {
    Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/gio-hang/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/gio-hang/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/gio-hang/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/gio-hang-ma-giam-gia', [CouponController::class, 'apply'])->middleware('throttle:10,1')->name('coupon.apply');
    Route::delete('/gio-hang-ma-giam-gia', [CouponController::class, 'remove'])->name('coupon.remove');
    Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');

    // Ownership is enforced by OrderPolicy.
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [OrderController::class, 'show'])->can('view', 'order')->name('orders.show');
    Route::get('/don-hang/{order}/phieu', [OrderController::class, 'receipt'])->can('view', 'order')->name('orders.receipt');
    Route::patch('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->can('cancel', 'order')->name('orders.cancel');

    Route::get('/yeu-thich', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/yeu-thich/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Verified-purchase reviews (ProductPolicy::review).
    Route::post('/san-pham/{product}/danh-gia', [ReviewController::class, 'store'])->can('review', 'product')->name('reviews.store');
    Route::delete('/san-pham/{product}/danh-gia', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::resource('products', AdminProductController::class)->except('show');
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    // Declare export before {order} so it cannot be treated as an order ID.
    Route::get('orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/receipt', [AdminOrderController::class, 'receipt'])->name('orders.receipt');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::resource('coupons', AdminCouponController::class)->except('show');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/lock', [AdminUserController::class, 'toggleLock'])->name('users.lock');
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
});
