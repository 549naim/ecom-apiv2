<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who placed the order
            $table->decimal('cart_total', 10, 2); // Total cart price before discount
            $table->decimal('total_discount', 10, 2); // Total discount applied
            $table->decimal('final_total', 10, 2); // Final payable amount after discount
            $table->string('coupon_code')->nullable(); // Applied coupon code
            $table->text('delivery_address')->nullable(); // Applied coupon code
            $table->enum('status', ['pending','processing' ,'completed', 'cancelled'])->default('pending'); // Order status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
