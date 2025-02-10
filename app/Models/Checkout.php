<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Checkout extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'cart_total', 'total_discount', 'final_total', 'coupon_code', 'status','delivery_address',
    ];

    public function items()
    {
        return $this->hasMany(CheckoutItem::class, 'checkout_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code'); // Assuming 'coupon_code' is the foreign key in checkout and 'code' is the primary key in coupon
    }
}
