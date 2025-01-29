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
        Schema::table('coupons', function (Blueprint $table) {
            // Modify product_id column to store multiple product IDs as JSON
            $table->json('product_id')->nullable()->after('chapped_on');  // JSON column for multiple product IDs
            
            // Minimum value for the coupon (decimal type)
            $table->decimal('min_value', 10, 2)->default(0)->after('product_id');  
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
          
        });
    }
};
