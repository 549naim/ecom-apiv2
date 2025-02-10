<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    // Fetch all banners
    public function index()
    {
        $banners = Banner::orderBy('serial_number')->get();
        return response()->json($banners);
    }

    // Store a new banner
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,png,jpeg,gif',
            'serial_number' => 'required|integer|unique:banners,serial_number',
            'button_text' => 'required|string|max:255',
            'button_link' => 'required',
        ]);

        // Store the image
        $imagePath = $request->file('image')->store('banners', 'public');

        // Create banner
        $banner = Banner::create([
            'image' => $imagePath,
            'serial_number' => $request->serial_number,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
        ]);

        return response()->json($banner, 201);
    }

    // Show a specific banner
    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json($banner);
    }

    // Update an existing banner
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        dd($request->all());

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif',
            'serial_number' => 'required|integer|unique:banners,serial_number',
            'button_text' => 'required|string|max:255',
            'button_link' => 'required',
        ]);

        // Update image if provided
        if ($request->hasFile('image')) {
            // Delete the old image
            Storage::delete('public/' . $banner->image);

            // Store the new image
            $imagePath = $request->file('image')->store('banners', 'public');
            $banner->image = $imagePath;
        }

        $banner->serial_number = $request->serial_number;
        $banner->button_text = $request->button_text;
        $banner->button_link = $request->button_link;
        $banner->save();

        return response()->json($banner);
    }

    // Delete a banner
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Delete the image
        Storage::delete('public/' . $banner->image);

        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }
}
