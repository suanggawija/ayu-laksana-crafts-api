<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // Check if the user is an admin
        // if ($user->is_admin) {
        if ($user->role == "admin") {
            // If the user is an admin, fetch all orders
            $orders = Order::all();
        } else {
            // If the user is not an admin, fetch only their orders
            $orders = Order::where('user_id', $user->id)->get();
        }

        // Return the orders as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_country' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:255',
            'items' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            // Merge the user ID into the request
            $request->merge(['user_id' => Auth::id()]);

            // Create a unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            $request->merge(['order_number' => $orderNumber]);

            // Calculate the total amount from the cart items
            $totalAmount = 0;
            $request->merge(['total_amount' => $totalAmount]);

            // Set the order status to 'pending'
            $request->merge(['status' => 'pending']);

            // Set the payment status to 'pending'
            $request->merge(['payment_status' => 'pending']);

            // Set the timestamps for shipping
            $request->merge(['shipped_at' => now()]);

            // Create a new order
            $order = Order::create($request->all());

            // Loop through the cart items and associate them with the order
            collect($request->items)->map(function ($item) use ($order) {
                $product = Product::where('id', $item['product_id'])->first();
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total_price' => $product->price * $item['quantity'],
                ]);
            });
            // Calculate the total amount for the order
            $totalAmount = OrderDetail::where('order_id', $order->id)->sum('total_price');
            $order->total_amount = $totalAmount;
            $order->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            // Handle the exception
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $th->getMessage(),
            ])->setStatusCode(500, 'Internal Server Error');
        }

        // Return the created order as a JSON response
        return response()->json([

            'status' => true,
            'message' => 'Order created successfully',
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        if ($user->id != $order->user_id && $user->role != "admin") {

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ])->setStatusCode(403, 'Forbidden');
        }
        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully',
            'data' => new OrderResource($order),
        ])->setStatusCode(200, 'OK');
    }


    public function update_delivered_at(Order $order)
    {
        try {
            switch ($order->status) {
                case 'delivered':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is already delivered',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'completed':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is already completed and cannot be changed back to delivered',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'cancelled':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order has been cancelled',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                default:
                    $order->delivered_at = now();
                    $order->status = 'delivered';
                    $order->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Order delivery status updated successfully',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(200, 'OK');
                    break;
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update delivery status',
                'error' => $th->getMessage(),
            ])->setStatusCode(500, 'Internal Server Error');
        }
    }

    public function update_completed_at(Order $order)
    {
        try {
            switch ($order->status) {
                case 'pending':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is already delivered and cannot be changed to completed. Please mark this order as delivered first.',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'completed':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order  already completed',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'cancelled':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order has been cancelled',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                default:
                    $order->delivered_at = now();
                    $order->status = 'completed';
                    $order->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Order completed status updated successfully',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(200, 'OK');
                    break;
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update delivery status',
                'error' => $th->getMessage(),
            ])->setStatusCode(500, 'Internal Server Error');
        }
    }

    public function update_cencelled_at(Order $order)
    {
        try {
            switch ($order->status) {
                case 'delivered':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is already delivered and cannot be cancelled',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'completed':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is already completed and cannot be cancelled',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'cancelled':
                    return response()->json([
                        'status' => false,
                        'message' => 'Order is already cancelled',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                default:
                    $order->cancelled_at = now();
                    $order->status = 'cancelled';
                    $order->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Order cancelled successfully',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(200, 'OK');
                    break;
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update delivery status',
                'error' => $th->getMessage(),
            ])->setStatusCode(500, 'Internal Server Error');
        }
    }

    public function update_payment_to_paid(Order $order)
    {
        try {
            switch ($order->payment_status) {
                case 'paid':
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment is already marked as paid',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'failed':
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment has failed and cannot be marked as paid',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                default:
                    $order->payment_status = 'paid';
                    $order->paid_at = now();
                    $order->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Payment status updated to paid successfully',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(200, 'OK');
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update payment status',
                'error' => $th->getMessage(),
            ])->setStatusCode(500, 'Internal Server Error');
        }
    }

    public function update_payment_to_failed(Order $order)
    {
        try {
            switch ($order->payment_status) {
                case 'failed':
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment is already marked as failed',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                case 'paid':
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment is already marked as paid and cannot be changed to failed',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(400, 'Bad Request');
                default:
                    $order->payment_status = 'failed';
                    $order->failed_at = now();
                    $order->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Payment status updated to failed successfully',
                        'data' => new OrderResource($order),
                    ])->setStatusCode(200, 'OK');
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update payment status',
                'error' => $th->getMessage(),
            ])->setStatusCode(500, 'Internal Server Error');
        }
    }
}
