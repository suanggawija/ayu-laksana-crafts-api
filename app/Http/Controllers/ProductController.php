<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => ProductResource::collection($products),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:product_categories,id',
            'image' => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Handle file upload if an image is provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/products', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $request->merge(['image_url' => $imageUrl]);
        }

        $product = Product::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ])->setStatusCode(201, 'Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => true,
            'message' => 'Product retrieved successfully',
            'data' => new ProductResource($product),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:product_categories,id',
            'image' => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Handle file upload if an image is provided
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($product->image_url) {
                $oldImagePath = str_replace(asset('storage/'), '', $product->image_url);
                // if (file_exists(storage_path('app/public/' . $oldImagePath))) {
                //     unlink(storage_path('app/public/' . $oldImagePath));
                // }
                Storage::disk('public')->delete($oldImagePath);
            }

            // Store the new image
            $imagePath = $request->file('image')->store('images/products', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $request->merge(['image_url' => $imageUrl]);
        }

        $product->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete the image if it exists
        if ($product->image_url) {
            $oldImagePath = str_replace(asset('storage/'), '', $product->image_url);
            Storage::disk('public')->delete($oldImagePath);
        }
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ])->setStatusCode(200, 'OK');
    }
}
