<?php

namespace Tests\Feature;

use App\Mail\OrderUpdateMail;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderNeedsAttention;
use App\Notifications\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CouponQueueNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $price = 1000000, int $stock = 10): Product
    {
        $category = Category::firstOrCreate(['slug' => 'thiet-bi'], ['name' => 'Thiết bị']);
        return Product::create([
            'category_id' => $category->id, 'name' => 'Sản phẩm ' . $price,
            'price' => $price, 'stock' => $stock, 'is_active' => true,
        ]);
    }

    private function coupon(array $attributes = []): Coupon
    {
        return Coupon::create($attributes + [
            'code' => 'GIAM10', 'type' => 'percent', 'value' => 10, 'min_order_amount' => 0, 'is_active' => true,
        ]);
    }

    private function checkout(): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('checkout.store'), [
            'recipient_name' => 'Khách demo', 'phone' => '0901234567', 'address' => 'Số 1 đường demo',
        ]);
    }

    // ---- Coupons ------------------------------------------------------------

    public function test_coupon_math_respects_cap_minimum_and_subtotal(): void
    {
        $percent = new Coupon(['type' => 'percent', 'value' => 10, 'max_discount' => 300000]);
        $this->assertSame(100000, $percent->discountFor(1000000));
        $this->assertSame(300000, $percent->discountFor(5000000)); // capped

        $fixed = new Coupon(['type' => 'fixed', 'value' => 200000]);
        $this->assertSame(150000, $fixed->discountFor(150000)); // never above subtotal

        $minimum = new Coupon(['type' => 'fixed', 'value' => 1000, 'min_order_amount' => 500000, 'is_active' => true]);
        $this->assertNotNull($minimum->unavailableReason(499999));
        $this->assertNull($minimum->unavailableReason(500000));
    }

    public function test_customer_applies_coupon_and_order_stores_discount_and_usage(): void
    {
        Mail::fake();
        $product = $this->product(price: 1000000);
        $coupon = $this->coupon(['max_discount' => 150000]);
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('cart.add', $product), ['quantity' => 2]);
        // Codes are case-insensitive.
        $this->post(route('coupon.apply'), ['coupon' => ' giam10 '])->assertSessionHas('success');
        $this->get(route('cart.index'))->assertOk()->assertSee('GIAM10')->assertSee('−150.000₫', false);
        $this->get(route('checkout.create'))->assertOk()->assertSee('1.850.000');

        $this->checkout()->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(2000000, $order->subtotal_amount);
        $this->assertSame(150000, $order->discount_amount);
        $this->assertSame(1850000, $order->total_amount);
        $this->assertSame('GIAM10', $order->coupon_code);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertNull(session('coupon'));
        $this->get(route('orders.show', $order))->assertSee('Mã GIAM10')->assertSee('1.850.000');
    }

    public function test_invalid_coupons_are_rejected_with_a_reason(): void
    {
        $product = $this->product(price: 100000);
        $this->coupon(['code' => 'HETHAN', 'expires_at' => now()->subDay()]);
        $this->coupon(['code' => 'TOITHIEU', 'min_order_amount' => 500000]);
        $this->coupon(['code' => 'HETLUOT', 'usage_limit' => 1]);
        Coupon::where('code', 'HETLUOT')->update(['used_count' => 1]);
        $this->coupon(['code' => 'TAT', 'is_active' => false]);

        $this->actingAs(User::factory()->create())->post(route('cart.add', $product), ['quantity' => 1]);
        foreach (['KHONGCO' => 'không tồn tại', 'HETHAN' => 'hết hạn', 'TOITHIEU' => 'tối thiểu',
            'HETLUOT' => 'hết lượt', 'TAT' => 'ngừng áp dụng'] as $code => $reason) {
            $this->post(route('coupon.apply'), ['coupon' => $code])->assertSessionHasErrors('coupon');
            $this->assertStringContainsString($reason, session('errors')->first('coupon'));
        }
        $this->assertNull(session('coupon'));
    }

    public function test_coupon_that_becomes_invalid_is_removed_before_checkout(): void
    {
        $product = $this->product(price: 600000);
        $this->coupon(['code' => 'DON500', 'type' => 'fixed', 'value' => 50000, 'min_order_amount' => 500000]);
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('coupon.apply'), ['coupon' => 'DON500'])->assertSessionHas('success');
        Coupon::where('code', 'DON500')->update(['is_active' => false]);

        $this->get(route('checkout.create'))->assertOk()->assertSee('Đã gỡ mã DON500');
        $this->assertNull(session('coupon'));
        $this->checkout()->assertRedirect();
        $this->assertSame(600000, Order::firstOrFail()->total_amount);
    }

    public function test_usage_limit_is_rechecked_at_checkout_and_released_on_cancel(): void
    {
        $product = $this->product(price: 300000);
        $coupon = $this->coupon(['code' => 'MOTLAN', 'usage_limit' => 1]);
        $first = User::factory()->create();
        $second = User::factory()->create();

        // Both customers apply the code while one usage is still available.
        $this->actingAs($second)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('coupon.apply'), ['coupon' => 'MOTLAN'])->assertSessionHas('success');
        $secondSession = session()->all();
        session()->flush(); // the first customer has their own session

        $this->actingAs($first)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('coupon.apply'), ['coupon' => 'MOTLAN']);
        $this->checkout()->assertRedirect();
        $this->assertSame(1, $coupon->fresh()->used_count);

        // The second checkout must not exceed the limit.
        $this->actingAs($second)->withSession($secondSession)->checkout()->assertSessionHasErrors('coupon');
        $this->assertSame(1, Order::count());

        // Cancelling the first order gives the usage back.
        $this->actingAs($first)->patch(route('orders.cancel', Order::firstOrFail()))->assertSessionHas('success');
        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_admin_manages_coupons_with_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.coupons.store'), [
            'code' => 'tet2027', 'type' => 'percent', 'value' => 150, 'is_active' => 1,
        ])->assertSessionHasErrors('value');
        $this->post(route('admin.coupons.store'), [
            'code' => 'có dấu', 'type' => 'fixed', 'value' => 10000, 'is_active' => 1,
        ])->assertSessionHasErrors('code');

        $this->post(route('admin.coupons.store'), [
            'code' => 'tet2027', 'type' => 'fixed', 'value' => 50000, 'max_discount' => 99999,
            'min_order_amount' => 300000, 'usage_limit' => 10, 'is_active' => 1,
            'starts_at' => '2027-01-01T00:00', 'expires_at' => '2027-02-01T00:00',
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::where('code', 'TET2027')->firstOrFail();
        $this->assertNull($coupon->max_discount); // cap only applies to percent coupons
        $this->get(route('admin.coupons.index'))->assertOk()->assertSee('TET2027')->assertSee('Chưa bắt đầu');

        $coupon->forceFill(['used_count' => 1])->save();
        $this->delete(route('admin.coupons.destroy', $coupon))->assertSessionHas('error');
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);

        $this->actingAs(User::factory()->create())->get(route('admin.coupons.index'))->assertForbidden();
    }

    // ---- Queue --------------------------------------------------------------

    public function test_order_email_is_pushed_to_the_queue_with_retries(): void
    {
        Queue::fake();
        $product = $this->product();
        $this->actingAs(User::factory()->create())->post(route('cart.add', $product), ['quantity' => 1]);
        $this->checkout()->assertRedirect()->assertSessionHas('success');

        $this->assertInstanceOf(ShouldQueue::class, new OrderUpdateMail(Order::firstOrFail()));
        Queue::assertPushed(\Illuminate\Mail\SendQueuedMailable::class, fn ($job) =>
            $job->mailable instanceof OrderUpdateMail && $job->tries === 3);
    }

    public function test_queued_email_keeps_the_status_it_was_created_for(): void
    {
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id, 'recipient_name' => 'Khách', 'phone' => '0901234567',
            'address' => 'Demo', 'total_amount' => 100000, 'status' => 'confirmed',
        ]);
        $mail = new OrderUpdateMail($order->fresh(), 'pending');
        $order->update(['status' => 'shipping']); // a later change before the worker runs

        $restored = unserialize(serialize($mail));
        $this->assertSame('confirmed', $restored->order->status);
        $this->assertStringContainsString('Đã xác nhận', $restored->envelope()->subject);
    }

    // ---- In-site notifications ----------------------------------------------

    public function test_checkout_notifies_customer_and_admins_in_site(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $this->actingAs($customer)->post(route('cart.add', $this->product()), ['quantity' => 1]);
        $this->checkout();
        $order = Order::firstOrFail();

        $this->assertSame(1, $customer->unreadNotifications()->count());
        $this->assertSame(OrderStatusChanged::class, $customer->notifications()->first()->type);
        $this->assertSame(1, $admin->unreadNotifications()->count());
        $this->assertSame(OrderNeedsAttention::class, $admin->notifications()->first()->type);
        $this->assertStringContainsString($order->code, $admin->notifications()->first()->data['message']);

        $this->get(route('home'))->assertSee('Thông báo, 1 chưa đọc');
    }

    public function test_status_changes_notify_customer_and_customer_cancellation_notifies_admin(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id, 'recipient_name' => 'Khách', 'phone' => '0901234567',
            'address' => 'Demo', 'total_amount' => 100000, 'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'confirmed']);
        $this->assertStringContainsString('xác nhận', $customer->notifications()->first()->data['message']);
        // Admin's own action does not notify Admins.
        $this->assertSame(0, $admin->notifications()->count());

        $second = Order::create([
            'user_id' => $customer->id, 'recipient_name' => 'Khách', 'phone' => '0901234567',
            'address' => 'Demo', 'total_amount' => 100000, 'status' => 'pending',
        ]);
        $this->actingAs($customer)->patch(route('orders.cancel', $second))->assertSessionHas('success');
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame('Khách đã hủy đơn', $admin->notifications()->first()->data['title']);
    }

    public function test_opening_a_notification_marks_it_read_and_is_private(): void
    {
        Mail::fake();
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($customer)->post(route('cart.add', $this->product()), ['quantity' => 1]);
        $this->checkout();
        $order = Order::firstOrFail();
        $notification = $customer->notifications()->first();

        $this->get(route('notifications.index'))->assertOk()->assertSee('Đặt hàng thành công');
        $this->actingAs($other)->get(route('notifications.open', $notification->id))->assertNotFound();

        $this->actingAs($customer)->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('orders.show', $order, false));
        $this->assertNotNull($notification->fresh()->read_at);

        $customer->notify(new OrderStatusChanged($order, 'pending'));
        $this->post(route('notifications.read-all'))->assertSessionHas('success');
        $this->assertSame(0, $customer->unreadNotifications()->count());
    }
}
