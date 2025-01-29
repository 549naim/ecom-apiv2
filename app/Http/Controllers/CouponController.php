<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CouponController extends Controller
{
    // Create a new coupon
    public function store(Request $request)
    {
        // Validate request
       dd($request->all());
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'chapped_on' => 'required|numeric|min:0',
            'product_id' => 'nullable|array',  // Validate product_id as an array
            'product_id.*' => 'integer|exists:products,id', // Validate each product_id exists in products table
            'min_value' => 'required|numeric|min:0',
        ]);
       
        // Store product_id as JSON (array format)
        $productIds = $request->product_id ? json_encode($request->product_id) : null;
       
        // Create the coupon
        $coupon = Coupon::create([
            'code' => $request->code,
            'discount_percentage' => $request->discount_percentage,
            'chapped_on' => $request->chapped_on,
            'product_id' => $productIds,  // Store product_id as JSON
            'min_value' => $request->min_value,
        ]);
    
        return response()->json($coupon, 201);
    }
    
    

    // Get all coupons
    public function index()
    {
        $coupons = Coupon::all();
        return response()->json($coupons);
    }

    // Get a specific coupon
    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, $id)
{
    // Find the coupon or fail
    $coupon = Coupon::findOrFail($id);

    // Validate the request
    $request->validate([
        'code' => 'required|string|unique:coupons,code,' . $coupon->id,
        'discount_percentage' => 'required|numeric|min:0|max:100',
        'chapped_on' => 'required|numeric|min:0',
        'product_id' => 'nullable|array',  // Validate product_id as an array
        'product_id.*' => 'integer|exists:products,id', // Validate each product_id exists in products table
        'min_value' => 'required|numeric|min:0',
    ]);

    // Update the coupon with the provided fields
    $productIds = $request->product_id ? json_encode($request->product_id) : $coupon->product_id;

    $coupon->update([
        'code' => $request->code,
        'discount_percentage' => $request->discount_percentage,
        'chapped_on' => $request->chapped_on,
        'product_id' => $productIds, // Update product_id
        'min_value' => $request->min_value,
    ]);

    return response()->json($coupon);
}

    

    // Delete a coupon
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }
    



    public function applyCoupon(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'cart' => 'required|array',
            'cart.*.product_id' => 'required|integer|exists:products,id',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $cart = $request->cart;
        $couponCode = $request->coupon_code;
    
        // Fetch coupon
        $coupon = Coupon::where('code', $couponCode)->first();
    
        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }
    
        // Check coupon conditions
        $totalDiscount = 0;
        $cartTotal = 0;
        $cartDetails = [];
    
        foreach ($cart as $item) {
            $productTotal = $item['price'] * $item['quantity'];
            $cartTotal += $productTotal;
    
            // Initialize product discount
            $productDiscount = 0;
    
            // Apply coupon only to matching product and minimum value condition
            if ($coupon->product_id && in_array($item['product_id'], json_decode($coupon->product_id))) {
                if ($productTotal >= $coupon->min_value) {
                    $productDiscount = $productTotal * ($coupon->discount_percentage / 100);
    
                    // Cap discount if it exceeds `chapped_on`
                    if ($productDiscount > $coupon->chapped_on) {
                        $productDiscount = $coupon->chapped_on;
                    }
    
                    // Add the discount to the total discount
                    $totalDiscount += $productDiscount;
                }
            }
    
            // Calculate final price for the product after discount
            $finalPrice = $productTotal - $productDiscount;
    
            // Store the product's details in the response
            $cartDetails[] = [
                'product_id' => $item['product_id'],
                'product_price' => $item['price'],
                'quantity' => $item['quantity'],
                'product_discount' => $productDiscount,
                'final_price' => $finalPrice,
            ];
        }
    
        // Calculate the final total after discount
        $finalTotal = $cartTotal - $totalDiscount;
    
        return response()->json([
            'cart_total' => $cartTotal,
            'total_discount' => $totalDiscount,
            'final_total' => $finalTotal,
            'cart_details' => $cartDetails,  // Include product details with final price and discount
        ]);
    }
    
    
}



