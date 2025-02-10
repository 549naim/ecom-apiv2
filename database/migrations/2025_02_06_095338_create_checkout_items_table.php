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
        Schema::create('checkout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_id')->constrained()->onDelete('cascade'); // Links to checkout
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Links to product
            $table->decimal('product_price', 10, 2); // Price per product
            $table->integer('quantity'); // Number of items
            $table->decimal('product_discount', 10, 2); // Discount on this product
            $table->decimal('final_price', 10, 2); // Final price after discount
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_items');
    }
};
