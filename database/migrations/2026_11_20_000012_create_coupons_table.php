<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();          // stored upper-case
            $table->string('description')->nullable();
            $table->string('type', 10);                     // percent | fixed
            $table->unsignedBigInteger('value');            // % (1..100) or VND
            $table->unsignedBigInteger('max_discount')->nullable(); // cap for percent coupons
            $table->unsignedBigInteger('min_order_amount')->default(0);
            $table->unsignedInteger('usage_limit')->nullable();     // NULL = unlimited
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            // total_amount stays the amount the customer pays (after discount).
            $table->unsignedBigInteger('subtotal_amount')->default(0)->after('note');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('subtotal_amount');
            $table->foreignId('coupon_id')->nullable()->after('discount_amount')->constrained()->nullOnDelete();
            $table->string('coupon_code', 40)->nullable()->after('coupon_id'); // kept if the coupon is deleted
        });

        // Existing orders had no discount: subtotal equals the total.
        DB::table('orders')->update(['subtotal_amount' => DB::raw('total_amount')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['subtotal_amount', 'discount_amount', 'coupon_code']);
        });
        Schema::dropIfExists('coupons');
    }
};
