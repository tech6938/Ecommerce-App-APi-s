<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartController extends Controller
{
    // -----------------------------------------------------------------------
    // Helper: get or create the authenticated user's cart (with items loaded)
    // -----------------------------------------------------------------------
    private function getCart(): Cart
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $cart->load([
            'items.product',
            'items.variant.attributeOptions.attribute',
            'items.variant.images',
        ]);
        return $cart;
    }

    // -----------------------------------------------------------------------
    // GET /api/cart
    // View all items in cart
    // -----------------------------------------------------------------------
    public function index(): JsonResponse
    {
        $cart = $this->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data'    => new CartResource($cart),
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/cart/add
    // Add a product (with optional variant) to cart
    //
    // Body:
    //   product_id  (required)
    //   variant_id  (optional)
    //   quantity    (optional, default 1)
    // -----------------------------------------------------------------------
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1|max:100',
        ]);

        $product   = Product::findOrFail($request->product_id);
        $variantId = $request->variant_id;
        $quantity  = $request->quantity ?? 1;

        // Validate variant belongs to the product
        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->firstOrFail();

            // Check stock
            if ($variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Available: {$variant->stock}",
                ], 422);
            }
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        // Check if same product+variant combo already in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $quantity;

            // Re-check stock for combined quantity
            if ($variantId) {
                $variant = $variant ?? ProductVariant::find($variantId);
                if ($variant->stock < $newQty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock. Available: {$variant->stock}, already in cart: {$existingItem->quantity}",
                    ], 422);
                }
            }

            $existingItem->update(['quantity' => $newQty]);
            $item = $existingItem;
        } else {
            $item = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'quantity'   => $quantity,
            ]);
        }

        $item->load([
            'product',
            'variant.attributeOptions.attribute',
            'variant.images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data'    => [
                'item' => new CartItemResource($item),
                'cart_summary' => $this->cartSummary($cart->id),
            ],
        ], 201);
    }

    // -----------------------------------------------------------------------
    // PATCH /api/cart/items/{cartItemId}/increase
    // Increase quantity by 1 (or by ?step=N)
    // -----------------------------------------------------------------------
    public function increase(Request $request, int $cartItemId): JsonResponse
    {
        $request->validate([
            'qty' => 'nullable|integer|min:1|max:100',
        ]);

        $item = $this->findOwnedItem($cartItemId);
        $qty = $request->qty ?? 1;

        if ($item->variant) {
            $newQty = $item->quantity + $qty;
            if ($item->variant->stock < $newQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Available: {$item->variant->stock}",
                ], 422);
            }
        }

        $item->increment('quantity', $qty);
        $item->load(['product', 'variant.attributeOptions.attribute', 'variant.images']);

        return response()->json([
            'success' => true,
            'message' => 'Quantity increased',
            'data'    => [
                'item'         => new CartItemResource($item),
                'cart_summary' => $this->cartSummary($item->cart_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // PATCH /api/cart/items/{cartItemId}/decrease
    // Decrease quantity by 1 (or by ?step=N). Removes item if qty reaches 0.
    // -----------------------------------------------------------------------
    public function decrease(Request $request, int $cartItemId): JsonResponse
    {
        $request->validate([
            'step' => 'nullable|integer|min:1|max:100',
        ]);

        $item = $this->findOwnedItem($cartItemId);
        $step = $request->step ?? 1;

        if ($item->quantity <= $step) {
            $cartId = $item->cart_id;
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart (quantity reached zero)',
                'data'    => [
                    'item'         => null,
                    'cart_summary' => $this->cartSummary($cartId),
                ],
            ]);
        }

        $item->decrement('quantity', $step);
        $item->load(['product', 'variant.attributeOptions.attribute', 'variant.images']);

        return response()->json([
            'success' => true,
            'message' => 'Quantity decreased',
            'data'    => [
                'item'         => new CartItemResource($item),
                'cart_summary' => $this->cartSummary($item->cart_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // PUT /api/cart/items/{cartItemId}
    // Update cart item: change quantity AND/OR switch to a different variant
    //
    // Body:
    //   quantity   (optional)
    //   variant_id (optional) — switch variant (must belong to same product)
    // -----------------------------------------------------------------------
    public function update(Request $request, int $cartItemId): JsonResponse
    {
        $request->validate([
            'quantity'   => 'nullable|integer|min:1|max:100',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $item = $this->findOwnedItem($cartItemId);

        DB::transaction(function () use ($request, $item) {
            $newVariantId = $request->has('variant_id') ? $request->variant_id : $item->variant_id;
            $newQty       = $request->quantity ?? $item->quantity;

            // Validate new variant belongs to same product
            if ($newVariantId && $newVariantId !== $item->variant_id) {
                $variant = ProductVariant::where('id', $newVariantId)
                    ->where('product_id', $item->product_id)
                    ->firstOrFail();
            }

            $newVariant = $newVariantId ? ProductVariant::find($newVariantId) : null;

            // Stock check
            if ($newVariant && $newVariant->stock < $newQty) {
                abort(422, "Insufficient stock. Available: {$newVariant->stock}");
            }

            // If changing variant — check for duplicate (same product+variant already in cart)
            if ($newVariantId !== $item->variant_id) {
                $conflict = CartItem::where('cart_id', $item->cart_id)
                    ->where('product_id', $item->product_id)
                    ->where('variant_id', $newVariantId)
                    ->where('id', '!=', $item->id)
                    ->first();

                if ($conflict) {
                    // Merge into existing item
                    $conflict->update(['quantity' => $conflict->quantity + $newQty]);
                    $item->delete();
                    $item = $conflict; // for response
                    return;
                }
            }

            $item->update([
                'variant_id' => $newVariantId,
                'quantity'   => $newQty,
            ]);
        });

        // Reload fresh (item may have been replaced by merged conflict)
        $item->refresh();
        $item->load(['product', 'variant.attributeOptions.attribute', 'variant.images']);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data'    => [
                'item'         => new CartItemResource($item),
                'cart_summary' => $this->cartSummary($item->cart_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/cart/items/{cartItemId}
    // Remove a specific item from cart
    // -----------------------------------------------------------------------
    public function remove(int $cartItemId): JsonResponse
    {
        $item   = $this->findOwnedItem($cartItemId);
        $cartId = $item->cart_id;

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            // 'data'    => [
            //     'cart_summary' => $this->cartSummary($cartId),
            // ],
        ]);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/cart/clear
    // Remove ALL items from cart
    // -----------------------------------------------------------------------
    public function clear(): JsonResponse
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            // 'data'    => [
            //     'cart_summary' => [
            //         'total_items'    => 0,
            //         'total_quantity' => 0,
            //         'subtotal'       => '0.00',
            //         'total_discount' => '0.00',
            //         'payable_amount' => '0.00',
            //     ],
            // ],
        ]);
    }

    // -----------------------------------------------------------------------
    // GET /api/cart/count
    // Quick count endpoint (for cart badge in app header)
    // -----------------------------------------------------------------------
    public function count(): JsonResponse
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        $totalQuantity = $cart
            ? $cart->items()->sum('quantity')
            : 0;

        $totalItems = $cart
            ? $cart->items()->count()
            : 0;

        return response()->json([
            'success' => true,
            'message' => 'Cart count retrieved',
            'data'    => [
                'total_items'    => $totalItems,
                'total_quantity' => (int) $totalQuantity,
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Find a cart item that belongs to the authenticated user.
     */
    private function findOwnedItem(int $cartItemId): CartItem
    {
        $cart = Cart::where('user_id', auth()->id())->first();
        if (!$cart) {
            throw new NotFoundHttpException('No cart found for this user. Please add items to cart first.');
        }
        $item = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->first();
        if (!$item) {
            throw new NotFoundHttpException('Cart item not found or does not belong to your cart.');
        }

        return $item;
    }

    /**
     * Return a fresh cart summary array (after mutation).
     */
    private function cartSummary(int $cartId): array
    {
        $cart = Cart::with('items')->find($cartId);

        if (!$cart) {
            return [
                'total_items'    => 0,
                'total_quantity' => 0,
                'subtotal'       => '0.00',
                'total_discount' => '0.00',
                'payable_amount' => '0.00',
            ];
        }

        // Load pricing relations for correct totals
        $cart->load([
            'items.product',
            'items.variant',
        ]);

        return [
            'total_items'    => $cart->items->count(),
            'total_quantity' => $cart->total_quantity,
            'subtotal'       => number_format($cart->total_price, 2, '.', ''),
            'total_discount' => number_format($cart->total_discount, 2, '.', ''),
            'payable_amount' => number_format($cart->payable_amount, 2, '.', ''),
        ];
    }
}
