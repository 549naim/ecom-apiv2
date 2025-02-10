<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checkout;
use App\Models\CheckoutItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'cart' => 'required|array',
            'cart.*.product_id' => 'required|integer|exists:products,id',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'delivery_address' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cart = $request->cart;
        $couponCode = $request->coupon_code;
        $deliveryAddress = $request->delivery_address;

        // Initialize totals
        $cartTotal = 0;
        $totalDiscount = 0;
        $cartDetails = [];

        // Fetch coupon (if provided)
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;

        foreach ($cart as $item) {
            $productTotal = $item['price'] * $item['quantity'];
            $cartTotal += $productTotal;
            $productDiscount = 0;

            // Apply coupon if valid for this product
            if ($coupon && $coupon->product_id && in_array($item['product_id'], json_decode($coupon->product_id))) {
                if ($productTotal >= $coupon->min_value) {
                    $productDiscount = $productTotal * ($coupon->discount_percentage / 100);
                    if ($productDiscount > $coupon->chapped_on) {
                        $productDiscount = $coupon->chapped_on;
                    }
                    $totalDiscount += $productDiscount;
                }
            }

            // Store product details
            $cartDetails[] = [
                'product_id' => $item['product_id'],
                'product_price' => $item['price'],
                'quantity' => $item['quantity'],
                'product_discount' => $productDiscount,
                'final_price' => $productTotal - $productDiscount,
            ];
        }

        // Calculate final total
        $finalTotal = $cartTotal - $totalDiscount;

        // Store checkout record
        $checkout = Checkout::create([
            'user_id' => auth()->id(),
            'cart_total' => $cartTotal,
            'total_discount' => $totalDiscount,
            'final_total' => $finalTotal,
            'coupon_code' => $couponCode,
            'delivery_address' => $deliveryAddress,
            'status' => 'pending',
        ]);

        // Store checkout items
        foreach ($cartDetails as $item) {
            CheckoutItem::create([
                'checkout_id' => $checkout->id,
                'product_id' => $item['product_id'],
                'product_price' => $item['product_price'],
                'quantity' => $item['quantity'],
                'product_discount' => $item['product_discount'],
                'final_price' => $item['final_price'],
            ]);
        }

        return response()->json([
            'message' => 'Checkout successful',
            'checkout' => $checkout,
            'items' => $cartDetails,
        ], 201);
    }
}

