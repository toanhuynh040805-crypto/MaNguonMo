<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StoreEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name = 'Thiết bị', string $slug = 'thiet-bi'): Category
    {
        return Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
    }

    private function product(string $name = 'Bàn phím demo', int $price = 100000, int $stock = 5, ?string $slug = null): Product
    {
        return Product::create([
            'category_id' => $this->category()->id, 'name' => $name, 'slug' => $slug,
            'price' => $price, 'stock' => $stock, 'is_active' => true,
        ]);
    }

    private function completedOrderFor(User $customer, Product $product, string $status = 'completed'): Order
    {
        $order = Order::create([
            'user_id' => $customer->id, 'recipient_name' => 'Khách', 'phone' => '0901234567',
            'address' => 'Địa chỉ', 'total_amount' => $product->price, 'status' => $status,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'price' => $product->price, 'quantity' => 1, 'subtotal' => $product->price,
        ]);

        return $order;
    }

    // ---- Stable slugs -------------------------------------------------------

    public function test_renaming_a_seeded_product_keeps_its_url_and_real_photo(): void
    {
        $this->artisan('db:seed', ['--class' => \Database\Seeders\ModernCatalogSeeder::class]);
        $phone = Product::where('slug', 'iphone-16-pro-128gb')->firstOrFail();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.products.update', $phone), [
                'category_id' => $phone->category_id, 'name' => 'iPhone 16 Pro bản demo',
                'slug' => $phone->slug, 'price' => $phone->price, 'stock' => $phone->stock, 'is_active' => 1,
            ])->assertRedirect(route('admin.products.index'));

        $phone->refresh();
        $this->assertSame('iphone-16-pro-128gb', $phone->slug);
        $this->assertStringContainsString('/images/real-products/iphone-16-pro-128gb.webp', $phone->image_url);
        $this->get(route('products.show', 'iphone-16-pro-128gb'))->assertOk()->assertSee('iPhone 16 Pro bản demo');
    }

    public function test_slug_is_generated_once_and_made_unique(): void
    {
        $first = $this->product('Chuột không dây');
        $this->assertSame('chuot-khong-day', $first->slug);

        // A different name that slugifies to an existing slug gets a suffix instead of failing.
        $second = $this->product('Chuột - không dây');
        $this->assertSame('chuot-khong-day-2', $second->slug);

        $first->update(['name' => 'Chuột không dây mới']);
        $this->assertSame('chuot-khong-day', $first->fresh()->slug);
    }

    public function test_admin_can_set_a_custom_slug_but_not_a_duplicate_one(): void
    {
        $existing = $this->product('Tai nghe A');
        $category = $this->category();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Tai nghe B', 'slug' => 'Tai Nghe Dac Biet',
            'price' => 200000, 'stock' => 3, 'is_active' => 1,
        ])->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Tai nghe B', 'slug' => 'tai-nghe-dac-biet']);

        $this->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Tai nghe C', 'slug' => $existing->slug,
            'price' => 200000, 'stock' => 3, 'is_active' => 1,
        ])->assertSessionHasErrors('slug');

        $this->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Tai nghe A',
            'price' => 200000, 'stock' => 3, 'is_active' => 1,
        ])->assertSessionHasErrors('name');
    }

    public function test_header_menu_cache_refreshes_when_a_category_is_renamed(): void
    {
        $category = $this->category('Laptop', 'laptop');
        $this->get(route('home'))->assertOk()->assertSee('Laptop');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.categories.update', $category), ['name' => 'Máy tính xách tay', 'slug' => 'laptop'])
            ->assertRedirect(route('admin.categories.index'));

        $this->get(route('home'))->assertOk()->assertSee('Máy tính xách tay');
    }

    // ---- Orders -------------------------------------------------------------

    public function test_customer_can_search_own_orders_by_product_name_only(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $mine = $this->completedOrderFor($owner, $this->product('Loa Bluetooth mini'), 'pending');
        $notMatching = $this->completedOrderFor($owner, $this->product('Webcam HD'), 'pending');
        $foreign = $this->completedOrderFor($other, Product::where('name', 'Loa Bluetooth mini')->first(), 'pending');

        $this->actingAs($owner)->get(route('orders.index', ['q' => 'Bluetooth']))
            ->assertOk()->assertSeeText($mine->code)
            ->assertDontSeeText($notMatching->code)->assertDontSeeText($foreign->code);
    }

    public function test_customer_cannot_cancel_after_confirmation_even_via_service_rules(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(stock: 3);
        $order = $this->completedOrderFor($customer, $product, 'confirmed');

        $this->actingAs($customer)->patch(route('orders.cancel', $order))->assertSessionHas('error');
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    // ---- Profile ------------------------------------------------------------

    public function test_changing_email_requires_current_password_but_changing_name_does_not(): void
    {
        $user = User::factory()->create(['email' => 'old@example.test']);
        $this->actingAs($user);

        $this->patch(route('profile.update'), ['name' => 'Tên mới', 'email' => 'old@example.test'])
            ->assertSessionHasNoErrors();
        $this->patch(route('profile.update'), ['name' => 'Tên mới', 'email' => 'new@example.test'])
            ->assertSessionHasErrors('current_password');
        $this->patch(route('profile.update'), ['name' => 'Tên mới', 'email' => 'new@example.test', 'current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');
        $this->assertSame('old@example.test', $user->fresh()->email);

        $this->patch(route('profile.update'), ['name' => 'Tên mới', 'email' => 'new@example.test', 'current_password' => 'password'])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@example.test', $user->fresh()->email);
    }

    // ---- Price filter -------------------------------------------------------

    public function test_storefront_filters_by_price_range(): void
    {
        $this->product('Cáp sạc giá rẻ', price: 150000);
        $this->product('Tai nghe tầm trung', price: 900000);
        $this->product('Laptop cao cấp', price: 25000000);

        $this->get(route('home', ['min_price' => 200000, 'max_price' => 1000000]))
            ->assertOk()->assertSee('Tai nghe tầm trung')
            ->assertDontSee('Cáp sạc giá rẻ')->assertDontSee('Laptop cao cấp');

        $this->get(route('home', ['min_price' => 500000, 'max_price' => 100000]))
            ->assertSessionHasErrors('max_price');
    }

    // ---- Wishlist -----------------------------------------------------------

    public function test_customer_can_toggle_wishlist_and_see_saved_products(): void
    {
        $product = $this->product('Đồng hồ thông minh');
        $customer = User::factory()->create();

        $this->actingAs($customer)->postJson(route('wishlist.toggle', $product))
            ->assertOk()->assertJson(['saved' => true, 'wishlist_count' => 1]);
        $this->get(route('wishlist.index'))->assertOk()->assertSee('Đồng hồ thông minh');

        $this->postJson(route('wishlist.toggle', $product))
            ->assertOk()->assertJson(['saved' => false, 'wishlist_count' => 0]);
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_wishlist_is_for_customers_only(): void
    {
        $product = $this->product();
        $this->get(route('wishlist.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('wishlist.toggle', $product))->assertForbidden();
    }

    // ---- Reviews ------------------------------------------------------------

    public function test_only_customers_with_a_completed_purchase_can_review(): void
    {
        $product = $this->product();
        $buyer = User::factory()->create();
        $this->completedOrderFor($buyer, $product, 'shipping');

        $this->actingAs($buyer)->post(route('reviews.store', $product), ['rating' => 5])->assertForbidden();
        $this->get(route('products.show', $product->slug))->assertSee('sau khi nhận hàng thành công');

        $this->completedOrderFor($buyer, $product, 'completed');
        $this->post(route('reviews.store', $product), ['rating' => 4, 'comment' => 'Gõ rất êm'])
            ->assertRedirect()->assertSessionHas('success');
        // Submitting again edits the same review instead of adding a second one.
        $this->post(route('reviews.store', $product), ['rating' => 5, 'comment' => 'Dùng lâu vẫn tốt'])
            ->assertSessionHas('success');

        $this->assertSame(1, Review::count());
        $this->assertDatabaseHas('reviews', ['user_id' => $buyer->id, 'product_id' => $product->id, 'rating' => 5]);
        $this->get(route('products.show', $product->slug))->assertOk()
            ->assertSee('Dùng lâu vẫn tốt')->assertSee('Đã mua hàng')->assertSee('Sửa đánh giá của bạn');
    }

    public function test_review_rating_is_validated_and_shown_on_cards(): void
    {
        $product = $this->product('Sản phẩm được khen');
        $buyer = User::factory()->create();
        $this->completedOrderFor($buyer, $product);

        $this->actingAs($buyer)->post(route('reviews.store', $product), ['rating' => 7])->assertSessionHasErrors('rating');

        $this->post(route('reviews.store', $product), ['rating' => 4]);
        $this->get(route('home'))->assertOk()->assertSee('4,0 (1)', false);
    }

    public function test_admin_can_moderate_reviews_but_cannot_write_them(): void
    {
        $product = $this->product();
        $buyer = User::factory()->create();
        $this->completedOrderFor($buyer, $product);
        $review = Review::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'rating' => 1, 'comment' => 'Nội dung xấu']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk()->assertSee('Nội dung xấu');
        $this->delete(route('admin.reviews.destroy', $review))->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->post(route('reviews.store', $product), ['rating' => 5])->assertForbidden();
    }

    // ---- Password reset -----------------------------------------------------

    public function test_password_reset_flow_uses_laravel_broker(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'forgot@example.test']);

        $this->get(route('login'))->assertSee(route('password.request'));
        $this->post(route('password.email'), ['email' => 'forgot@example.test'])->assertSessionHas('success');
        // Unknown addresses get the same answer (no account enumeration) and no email.
        $this->post(route('password.email'), ['email' => 'nobody@example.test'])->assertSessionHas('success');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token, $user): bool {
            $token = $notification->token;
            return str_contains($notification->toMail($user)->subject, 'Đặt lại mật khẩu');
        });
        Notification::assertCount(1);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();
        $this->post(route('password.update'), [
            'token' => 'invalid-token', 'email' => $user->email,
            'password' => 'mat-khau-moi-123', 'password_confirmation' => 'mat-khau-moi-123',
        ])->assertSessionHasErrors('email');
        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'mat-khau-moi-123', 'password_confirmation' => 'mat-khau-moi-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('mat-khau-moi-123', $user->fresh()->password));
    }

    // ---- User management ----------------------------------------------------

    public function test_admin_can_lock_customer_who_then_cannot_use_the_site(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['email' => 'locked@example.test']);

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'locked@']))
            ->assertOk()->assertSee('locked@example.test');
        $this->patch(route('admin.users.lock', $customer))->assertSessionHas('success');
        $this->assertTrue($customer->fresh()->isLocked());

        // An existing session is ended on the next request.
        $this->actingAs($customer->fresh())->get(route('orders.index'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post(route('login.store'), ['email' => 'locked@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)->patch(route('admin.users.lock', $customer))->assertSessionHas('success');
        $this->assertFalse($customer->fresh()->isLocked());
    }

    public function test_admin_accounts_cannot_be_locked_and_customers_cannot_manage_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('admin.users.lock', $otherAdmin))->assertSessionHas('error');
        $this->assertFalse($otherAdmin->fresh()->isLocked());

        $this->actingAs(User::factory()->create())->get(route('admin.users.index'))->assertForbidden();
    }

    // ---- Dashboard ----------------------------------------------------------

    public function test_dashboard_lists_best_selling_products_from_completed_orders(): void
    {
        $customer = User::factory()->create();
        $bestSeller = $this->product('Sản phẩm bán chạy nhất');
        $cancelledOnly = $this->product('Sản phẩm chỉ bị hủy');
        $this->completedOrderFor($customer, $bestSeller);
        $this->completedOrderFor($customer, $cancelledOnly, 'cancelled');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Sản phẩm bán chạy')->assertSee('Sản phẩm bán chạy nhất')
            ->assertDontSee('Sản phẩm chỉ bị hủy');
    }
}
