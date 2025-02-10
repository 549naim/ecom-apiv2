<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checkout;
use App\Models\CheckoutItem;

class OrderController extends Controller
{
    /**
     * Get all orders (For Admin)
     */
    public function index()
    {
        $orders = Checkout::orderBy('created_at', 'desc')->get();
        return response()->json($orders);
    }
    

    /**
     * Get a single order with its items
     */
    public function show($id)
    {
        $order = Checkout::with('items')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * Update order status (For Admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Checkout::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Validate status input
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,canceled'
        ]);

        // Update status
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    public function userOrders(Request $request)
    {
        $user = $request->user(); // Get the authenticated user

        $orders = Checkout::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($orders);
    }

    public function orderDetails($id)
{
    $user = auth()->user(); // Get the authenticated user

    // Get the order by ID for the authenticated user
    $order = Checkout::where('user_id', $user->id)
                     ->with([
                         'items.product', // Include the product details for each item
                         'coupon'         // Include the coupon details if applied
                     ])
                     ->find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found or you do not have permission to view this order'], 404);
    }

    return response()->json($order);
}

}
