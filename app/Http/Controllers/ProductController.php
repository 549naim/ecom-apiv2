<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Fetch all products
    public function index(Request $request)
    {
        // Get limit and offset from request, default to 20 and 0 respectively
        $limit = $request->query('limit', 20);
        $offset = $request->query('offset', 0);

        // Retrieve all product fields with limit and offset
        $products = Product::skip($offset)
            ->take($limit)
            ->get();

        return response()->json($products);
    }


    // Store a new product
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock' => 'required|integer',
            'sku' => 'required|string|unique:products,sku',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Store the image in 'public' disk
            $imagePath = $request->file('image')->store('product_images', 'public');
        }

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'stock' => $request->stock,
            'sku' => $request->sku,
        ]);

        // Generate the full URL for the image
        $product->image_url = $imagePath ? Storage::url($imagePath) : null;

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    // Show a single product
    public function show($id)
    {
        $product = Product::findOrFail($id); // Find product by ID

        return response()->json($product);
    }

    // Update an existing product
    public function update(Request $request, $id)
    {
        // Find the product
        $product = Product::findOrFail($id);

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock' => 'required|integer',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle image upload (only if provided)
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::delete('public/' . $product->image);
            }
            // Store the new image in 'public' disk
            $imagePath = $request->file('image')->store('product_images', 'public');
        } else {
            $imagePath = $product->image; // Retain the old image if no new image is uploaded
        }

        // Update the product
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'stock' => $request->stock,
            'sku' => $request->sku,
        ]);

        // Generate the full URL for the image
        $product->image_url = $imagePath ? Storage::url($imagePath) : null;

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete image from storage if exists
        if ($product->image) {
            Storage::delete('public/products/' . $product->image);
        }

        // Delete the product
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
