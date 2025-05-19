<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = Product::query();

        // Filter by search term
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $products = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
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

        // // Handle file upload if an image is provided
        // if ($request->hasFile('image')) {
        //     // Delete the old image if it exists
        //     if ($product->image_url) {
        //         $oldImagePath = str_replace(asset('storage/'), '', $product->image_url);
        //         // if (file_exists(storage_path('app/public/' . $oldImagePath))) {
        //         //     unlink(storage_path('app/public/' . $oldImagePath));
        //         // }
        //         Storage::disk('public')->delete($oldImagePath);
        //     }

        //     // Store the new image
        //     $imagePath = $request->file('image')->store('images/products', 'public');
        //     $imageUrl = asset('storage/' . $imagePath);
        //     $request->merge(['image_url' => $imageUrl]);
        // }

        // $product->update($request->all());
        // Default image URL remains unchanged if no new image is uploaded
        $imageUrl = $product->image_url;

        // Jika ada file gambar baru yang diunggah
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image_url) {
                $oldImagePath = str_replace(asset('storage/'), '', $product->image_url);
                Storage::disk('public')->delete($oldImagePath);
            }

            // Simpan gambar baru
            $imagePath = $request->file('image')->store('images/products', 'public');
            $imageUrl = asset('storage/' . $imagePath);
        }

        // Update data produk
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'status' => $request->status,
            'image_url' => $imageUrl,
        ]);

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

    public function mostOrdered(Request $request)
    {
        $limit = $request->get('limit', 5);

        // Ambil produk dengan jumlah quantity terbanyak di order_details
        $products = DB::table('order_details')
            ->select('product_id', DB::raw('SUM(quantity) as total_ordered'))
            ->groupBy('product_id')
            ->orderByDesc('total_ordered')
            ->limit($limit)
            ->get();

        // Ambil data produk lengkap berdasarkan urutan hasil di atas
        $productIds = $products->pluck('product_id')->toArray();
        $orderedProducts = Product::whereIn('id', $productIds)->get();

        // Urutkan sesuai urutan hasil query agregat
        $orderedProducts = $orderedProducts->sortBy(function ($product) use ($productIds) {
            return array_search($product->id, $productIds);
        })->values();

        // Jika jumlah produk kurang dari limit, tambahkan produk lain berdasarkan id
        if ($orderedProducts->count() < $limit) {
            $remaining = $limit - $orderedProducts->count();
            $otherProducts = Product::whereNotIn('id', $productIds)
                ->orderBy('id')
                ->limit($remaining)
                ->get();

            // Gabungkan
            $orderedProducts = $orderedProducts->concat($otherProducts);
        }

        // Tambahkan total_ordered ke resource (0 jika belum pernah diorder)
        $result = $orderedProducts->map(function ($product) use ($products) {
            $total = $products->firstWhere('product_id', $product->id)->total_ordered ?? 0;
            $resource = new ProductResource($product);
            return array_merge($resource->toArray(request()), ['total_ordered' => (int)$total]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Most ordered products retrieved successfully',
            'data' => $result,
        ]);
    }
}
