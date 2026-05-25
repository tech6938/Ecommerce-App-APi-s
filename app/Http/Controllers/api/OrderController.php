<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Create a new order from selected cart items
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'cart_item_ids' => 'required|array|min:1', // IDs of cart items to checkout
            'cart_item_ids.*' => 'exists:cart_items,id',
            'customer_note' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|exists:coupons,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = auth()->id();

        // Get only selected cart items
        $cartItems = CartItem::whereIn('id', $request->cart_item_ids)
            ->whereHas('cart', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['product', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid cart items selected'
            ], 400);
        }

        $paymentMethod = PaymentMethod::find($request->payment_method_id);

        if (!$paymentMethod || !$paymentMethod->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected payment method is not available'
            ], 400);
        }

        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->variant_id) {
                if ($item->variant->stock < $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$item->product->title} - {$item->variant->sku}. Available: {$item->variant->stock}"
                    ], 400);
                }
            } else {
                if ($item->product->stock < $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$item->product->title}. Available: {$item->product->stock}"
                    ], 400);
                }
            }
        }

        DB::beginTransaction();

        try {
            // Calculate order amounts for selected items only
            $subtotal = $cartItems->sum(function($item) {
                $price = $item->discount_price ?? $item->unit_price;
                return $price * $item->quantity;
            });

            $discountAmount = $cartItems->sum(function($item) {
                if ($item->discount_price && $item->unit_price > $item->discount_price) {
                    return ($item->unit_price - $item->discount_price) * $item->quantity;
                }
                return 0;
            });

            // Calculate COD charge if applicable
            $codCharge = 0;
            if ($paymentMethod->code === 'cod') {
                $codCharge = $this->calculateCODCharge($subtotal);
            }

            $shippingCharge = 0; // Calculate based on your shipping logic
            $totalAmount = $subtotal - $discountAmount + $shippingCharge + $codCharge;

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'address_id' => $request->address_id,
                'payment_method_id' => $request->payment_method_id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_charge' => $shippingCharge,
                'cod_charge' => $codCharge,
                'total_amount' => $totalAmount,
                'coupon_code' => $request->coupon_code,
                'coupon_discount' => 0,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'customer_note' => $request->customer_note,
            ]);

            // Create order items from selected cart items
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;

                $unitPrice = $cartItem->discount_price ?? $cartItem->unit_price;
                $totalPrice = $unitPrice * $cartItem->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'product_name' => $product->title,
                    'product_sku' => $product->sku ?? null,
                    'variant_name' => $variant ? $variant->sku : null,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'discount_price' => $cartItem->discount_price ?? 0,
                    'total_price' => $totalPrice,
                    'selected_options' => $cartItem->selected_options,
                    'product_image' => $product->thumbnail,
                ]);

                // Update stock
                if ($variant) {
                    $variant->decrement('stock', $cartItem->quantity);
                } else {
                    $product->decrement('stock', $cartItem->quantity);
                }

                // Delete only the selected cart items (not the entire cart)
                $cartItem->delete();
            }

            DB::commit();

            // Load relationships
            $order->load(['items', 'address', 'paymentMethod', 'user']);

            // Get remaining cart items count
            $remainingCart = Cart::where('user_id', $userId)->first();
            $remainingItemsCount = $remainingCart ? $remainingCart->items()->count() : 0;

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => new OrderResource($order),
                    'remaining_cart_items' => $remainingItemsCount,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Order creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Please try again.'
            ], 500);
        }
    }

    /**
     * Get cart items grouped (for checkout selection)
     */
    public function getCheckoutItems(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'message' => 'Cart is empty',
                'data' => [
                    'items' => [],
                    'summary' => [
                        'total_items' => 0,
                        'total_quantity' => 0,
                        'subtotal' => 0,
                        'total' => 0,
                    ]
                ]
            ]);
        }

        $cartItems = CartItem::where('cart_id', $cart->id)
            ->with(['product', 'variant'])
            ->get();

        $items = $cartItems->map(function($item) {
            $price = $item->discount_price ?? $item->unit_price;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->title,
                'product_image' => $item->product->thumbnail ? url($item->product->thumbnail) : null,
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant ? $item->variant->sku : null,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_price' => $item->discount_price ? (float) $item->discount_price : null,
                'effective_price' => (float) $price,
                'total_price' => (float) ($price * $item->quantity),
                'selected_options' => $item->selected_options,
                'is_selected' => false, // For frontend selection
            ];
        });

        $subtotal = $items->sum('total_price');

        return response()->json([
            'success' => true,
            'message' => 'Checkout items retrieved successfully',
            'data' => [
                'items' => $items,
                'summary' => [
                    'total_items' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                    'subtotal' => (float) $subtotal,
                ],
                'select_all' => true,
            ]
        ]);
    }

    /**
     * Get order summary (for checkout page)
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $cart = Cart::where('user_id', $userId)
            ->with('items.product', 'items.variant')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        $subtotal = $cart->total_price;
        $discount = $cart->total_discount;
        $shippingCharge = 0;

        // Get selected payment method for COD charge
        $paymentMethodId = $request->get('payment_method_id');
        $codCharge = 0;

        if ($paymentMethodId) {
            $paymentMethod = PaymentMethod::find($paymentMethodId);
            if ($paymentMethod && $paymentMethod->code === 'cod') {
                $codCharge = $this->calculateCODCharge($subtotal);
            }
        }

        $total = $subtotal - $discount + $shippingCharge + $codCharge;

        return response()->json([
            'success' => true,
            'message' => 'Order summary retrieved successfully',
            'data' => [
                'subtotal' => (float) $subtotal,
                'discount' => (float) $discount,
                'shipping_charge' => (float) $shippingCharge,
                'cod_charge' => (float) $codCharge,
                'total' => (float) $total,
                'items_count' => $cart->items->count(),
                'total_quantity' => $cart->total_quantity,
            ]
        ]);
    }

    /**
     * Preview order before placing (calculate totals for selected items)
     */
    public function previewOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'exists:cart_items,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'address_id' => 'nullable|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = auth()->id();

        $cartItems = CartItem::whereIn('id', $request->cart_item_ids)
            ->whereHas('cart', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['product', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid cart items selected'
            ], 400);
        }

        // Calculate amounts
        $subtotal = $cartItems->sum(function($item) {
            $price = $item->discount_price ?? $item->unit_price;
            return $price * $item->quantity;
        });

        $discount = $cartItems->sum(function($item) {
            if ($item->discount_price && $item->unit_price > $item->discount_price) {
                return ($item->unit_price - $item->discount_price) * $item->quantity;
            }
            return 0;
        });

        // Calculate COD charge if payment method is selected
        $codCharge = 0;
        if ($request->payment_method_id) {
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            if ($paymentMethod && $paymentMethod->code === 'cod') {
                $codCharge = $this->calculateCODCharge($subtotal);
            }
        }

        $shippingCharge = 0;
        $total = $subtotal - $discount + $shippingCharge + $codCharge;

        $items = $cartItems->map(function($item) {
            $price = $item->discount_price ?? $item->unit_price;

            return [
                'id' => $item->id,
                'name' => $item->product->title,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_price' => $item->discount_price ? (float) $item->discount_price : null,
                'effective_price' => (float) $price,
                'total' => (float) ($price * $item->quantity),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Order preview calculated successfully',
            'data' => [
                'items' => $items,
                'items_count' => $cartItems->count(),
                'total_quantity' => $cartItems->sum('quantity'),
                'subtotal' => (float) $subtotal,
                'discount' => (float) $discount,
                'shipping_charge' => (float) $shippingCharge,
                'cod_charge' => (float) $codCharge,
                'total' => (float) $total,
            ]
        ]);
    }

    /**
     * Calculate COD charge based on order amount
     */
    private function calculateCODCharge($amount)
    {
        if ($amount > 50000) {
            return 0;
        }
        return 1000;
    }

    /**
     * Get user's orders
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $limit = $request->get('limit', 20);
        $status = $request->get('status');

        $query = Order::where('user_id', $userId)
            ->with(['items', 'address', 'paymentMethod'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('order_status', $status);
        }

        $orders = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => OrderResource::collection($orders),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]
        ]);
    }

    /**
     * Get single order details
     */
    public function show($id): JsonResponse
    {
        $userId = auth()->id();

        $order = Order::where('user_id', $userId)
            ->with(['items', 'address', 'paymentMethod'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel($id): JsonResponse
    {
        $userId = auth()->id();

        $order = Order::where('user_id', $userId)
            ->whereIn('order_status', ['pending', 'confirmed'])
            ->findOrFail($id);

        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)
                        ->increment('stock', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'order_status' => 'cancelled',
                'payment_status' => 'failed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => new OrderResource($order->fresh(['items', 'address', 'paymentMethod']))
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order'
            ], 500);
        }
    }
}
