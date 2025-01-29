<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Get limit and offset from request, default to 20 and 0 respectively
        $limit = $request->query('limit', 20);
        $offset = $request->query('offset', 0);
    
        // Retrieve all products with category name and review count
        $products = Product::with('category') // Assuming the relationship is defined as 'category' in the Product model
            ->withCount('reviews') // Adding the review count
            ->skip($offset)
            ->take($limit)
            ->get();
    
        // Transform the products to include category name and review count in the response
        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image,
                'stock' => $product->stock,
                'sku' => $product->sku,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                'category' => $product->category ? $product->category->name : null, // Include category name
                'review_count' => $product->reviews_count, // Include review count
            ];
        });
    
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
            'category_id' => $request->category_id,
            'slug' => Str::slug($request->name),
        ]);

        // Generate the full URL for the image
        $product->image_url = $imagePath ? Storage::url($imagePath) : null;

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

            // Show a single product
        // Show a single product
        public function show($id)
        {
            // Find product by ID with the associated category and reviews count
            $product = Product::with('category')
                ->withCount('reviews') // Add review count
                ->findOrFail($id);

            // Return the product with category name and review count
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image,
                'stock' => $product->stock,
                'sku' => $product->sku,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                'category' => $product->category ? $product->category->name : null, // Include category name
                'review_count' => $product->reviews_count, // Include review count
            ]);
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
            'category_id' => $request->category_id,
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

    public function searchByName($name)
    {
        $products = Product::where('name', 'like', '%' . $name . '%')
            ->orWhere('sku', 'like', '%' . $name . '%')
            ->get();
    
        return response()->json($products);
    }

    public function filterByCategory($categoryId)
{
    // Retrieve products belonging to the specified category
    $products = Product::where('category_id', $categoryId)
        ->with('category') // Load category details if needed
        ->get();

    // Return the products as JSON response
    return response()->json($products);
}

public function filterByPriceAndSort(Request $request)
{
    // Validate the request inputs
    $validated = $request->validate([
        'min_price' => 'nullable|numeric|min:0',
        'max_price' => 'nullable|numeric|min:0',
        'sort' => 'nullable|in:asc,desc',
    ]);

    // Extract values from request, with defaults
    $minPrice = $validated['min_price'] ?? 0; // Default to 0
    $maxPrice = $validated['max_price'] ?? null; // No upper limit by default
    $sortDirection = $validated['sort'] ?? 'asc'; // Default to ascending order

    // Build the query
    $query = Product::query();

    // Apply price range filter
    if (!is_null($minPrice)) {
        $query->where('price', '>=', $minPrice);
    }
    if (!is_null($maxPrice)) {
        $query->where('price', '<=', $maxPrice);
    }

    // Apply sorting
    $query->orderBy('price', $sortDirection);

    // Retrieve the filtered and sorted products
    $products = $query->with('category')->get();

    // Return the response as JSON
    return response()->json($products);
}


    

}