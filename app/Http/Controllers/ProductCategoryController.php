<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productCategorys = ProductCategory::all();
        return response()->json([
            'status' => true,
            'message' => 'Product Categorys retrieved successfully',
            'data' => ProductCategoryResource::collection($productCategorys),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $productCategory = ProductCategory::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product Category created successfully',
            'data' => new ProductCategoryResource($productCategory),
        ])->setStatusCode(201, 'Created');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
            return response()->json([
                'status' => false,
                'message' => 'Product Category not found',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Product Category retrieved successfully',
            'data' => new ProductCategoryResource($productCategory),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
            return response()->json([
                'status' => false,
                'message' => 'Product Category not found',
            ], 404);
        }

        $productCategory->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product Category updated successfully',
            'data' => new ProductCategoryResource($productCategory),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
            return response()->json([
                'status' => false,
                'message' => 'Product Category not found',
            ], 404);
        }

        $productCategory->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product Category deleted successfully',
        ])->setStatusCode(200, 'OK');
    }
}
