<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all carts
        $carts = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        // Return the carts as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Carts retrieved successfully',
            'data' => $carts,
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $request->merge(['user_id' => Auth::id()]);

        // Calculate the total price
        $product = Product::find($request->product_id);
        $totalPrice = $product->price * $request->quantity;
        $request->merge(['total_price' => $totalPrice]);
        // Check if the product already exists in the cart

        // Create a new cart item
        $cart = Cart::create($request->all());

        // Return the created cart item as a JSON response
        return response()->json($cart, 201);
    }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Cart $cart)
    // {

    //     // Return the cart item as a JSON response
    //     return response()->json($cart);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        // Validate the request
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        // Check if the cart item exists
        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found',
            ])->setStatusCode(404, 'Not Found');
        }

        // Check if the cart item belongs to the authenticated user
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ])->setStatusCode(403, 'Forbidden');
        }

        // Calculate the total price
        $product = Product::find($cart->product_id);
        $totalPrice = $product->price * $request->quantity;
        $request->merge(['total_price' => $totalPrice]);

        // Update the cart item
        $cart->update($request->all());

        // Return the updated cart item as a JSON response
        return response()->json($cart, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        // Check if the cart item belongs to the authenticated user
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ])->setStatusCode(403, 'Forbidden');
        }

        // Delete the cart item
        $cart->delete();

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Cart item deleted successfully',
        ])->setStatusCode(200, 'OK');
    }
}
