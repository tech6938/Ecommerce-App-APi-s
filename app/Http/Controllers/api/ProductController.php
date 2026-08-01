<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductResource;
use App\Models\Currency;
use App\Models\Banner;
use App\Models\Category;
use App\Models\CodCharge;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductLike;
use App\Models\RecentlyViewed;
use App\Traits\TracksProductViews;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use TracksProductViews;
    /**
     * banners list
     */
    public function banners(Request $request)
    {
        $banners = Banner::where('status', 'active')->get();
        return response()->json([
            'success' => true,
            'message' => 'Banners retrieved successfully',
            'data' => $banners
        ]);
    }

    /**
     * categories list
     */
    public function categories()
    {
        $categories = Category::where('status', 'active')->get();
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * products list
     */
    public function products(Request $request)
    {
        $userId = auth()->id();

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $products = Product::where('status', 'active')
            ->withAvg('ratings', 'rating')
            ->withCount(['ratings', 'likes'])
            ->with(['userLike' => fn($query) => $query->where('user_id', $userId)])
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                ]
            ]
        ]);
    }

    /**
     * product details list
     */
    public function productDetails($id)
    {
        $userId = auth()->id();

        $product = Product::where('status', 'active')
            ->with([
                'category:id,title',
                'images' => function ($query) {
                    $query->select('id', 'product_id', 'image', 'sort_order', 'attribute_option_id')
                        ->with([
                            'taggedOption:id,attribute_id,value,hex_code',
                            'taggedOption.attribute:id,name,display_type',
                        ]);
                },
                'variants' => function ($query) {
                    $query->select('id', 'product_id', 'sku', 'price', 'discount_price', 'stock', 'image', 'is_default')
                        ->orderByDesc('is_default')
                        ->orderBy('id')
                        ->with([
                            'attributeOptions:id,attribute_id,value,hex_code',
                            'attributeOptions.attribute:id,name,display_type',
                            'images:id,variant_id,image',
                        ]);
                },
            ])
            ->withAvg('ratings', 'rating')
            ->withCount(['ratings', 'likes'])
            ->when($userId, fn($query) => $query->with(['userLike' => fn($subQuery) => $subQuery->where('user_id', $userId)]))
            ->findOrFail($id);

        $this->trackProductView($product->id);

        return response()->json([
            'success' => true,
            'message' => 'Product fetched successfully',
            'data' => new ProductDetailsResource($product)
        ], 200);
    }

    /**
     * Toggle like/dislike for a product
     */
    public function toggleLike($id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        $like = ProductLike::firstOrNew([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $like->is_like = $like->exists ? !$like->is_like : true;
        $like->save();

        return response()->json([
            'success' => true,
            'message' => $like->is_like ? 'Product liked successfully' : 'Product disliked successfully',
            'data' => [
                'product_id' => $product->id,
                'is_like' => $like->is_like,
            ],
        ], 200);
    }

    /**
     * Get products liked by the authenticated user
     */
    public function likedProducts(Request $request)
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => []
            ], 401);
        }

        $query = Product::whereHas('likes', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_like', true);
        })
            ->where('status', 'active')
            ->withAvg('ratings', 'rating')
            ->withCount(['ratings', 'likes'])
            ->with(['userLike' => fn($q) => $q->where('user_id', $userId)])
            ->latest();

        // Optional pagination support
        if ($limit = $request->get('limit')) {
            $page = $request->get('page', 1);
            $products = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Liked products retrieved successfully',
                'like_count' => $products->total() ?? 0,
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Liked products retrieved successfully',
            'like_count' => $products->count(),
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Get latest 10 recently viewed products
     */
    public function recentViewdProducts()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => true,
                'message' => 'Login to see your recent views',
                'data' => []
            ]);
        }

        $recentViews = RecentlyViewed::with(['product' => function ($query) {
            $query->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->with(['userLike' => fn($query) => $query->where('user_id', auth()->id())]);
        }])
            ->where('user_id', $userId)
            ->orderBy('viewed_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Recent products retrieved successfully',
            'data' => [
                'total' => $recentViews->count(),
                'products' => ProductResource::collection($recentViews->pluck('product'))
            ]
        ]);
    }

    /**
     * autocomplete search list
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1'
        ]);

        $searchTerm = $request->q;
        $currency = Currency::current();

        $products = Product::where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('brand', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('title', 'LIKE', "%{$searchTerm}%");
                    });
            })
            ->with('category')
            ->limit(10)
            ->get(['id', 'title', 'brand', 'thumbnail', 'price', 'discount_price']);

        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                $currency = Currency::current();
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'brand' => $product->brand,
                    'thumbnail' => $product->thumbnail ? url($product->thumbnail) : null,
                    'price' => (float) $product->price,
                    'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                    'currency' => $currency?->symbol ?: $currency?->currency_code ?: '$',
                    'currency_position' => $currency?->symbol_position,
                ];
            })
        ]);
    }

    /**
     * Get products by category ID with pagination
     */
    public function productsByCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $categoryId = $request->category_id;
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        // Check if category exists
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data' => []
            ], 404);
        }

        $userId = auth()->id();

        $products = Product::where('category_id', $categoryId)
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->withAvg('ratings', 'rating')
            ->withCount(['ratings', 'likes'])
            ->with(['userLike' => fn($query) => $query->where('user_id', $userId)])
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->title,
                    // 'slug' => $category->slug ?? null,
                ],
                'products' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                ]
            ]
        ]);
    }

    /**
     * Autocomplete search within liked products
     */
    public function autocompleteLikedProducts(Request $request)
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => []
            ], 401);
        }

        $request->validate([
            'q' => 'required|string|min:1'
        ]);

        $searchTerm = $request->q;
        $currency = Currency::current();

        $products = Product::whereHas('likes', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_like', true);
        })
            ->where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('brand', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('title', 'LIKE', "%{$searchTerm}%");
                    });
            })
            ->with('category')
            ->limit(10)
            ->get(['id', 'title', 'brand', 'thumbnail', 'price', 'discount_price']);
        // return $products;

        return response()->json([
            'success' => true,
            'message' => 'Autocomplete results from liked products',
            'data' => [
                'search_term' => $searchTerm,
                'total_results' => $products->count(),
                'products' => $products->map(function ($product) use ($currency) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'brand' => $product->brand,
                        'category' => $product->category ? $product->category->title : null,
                        'thumbnail' => $product->thumbnail ? url($product->thumbnail) : null,
                        'price' => (float) $product->price,
                        'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                        'currency' => $currency?->symbol ?: $currency?->currency_code ?: '$',
                        'currency_position' => $currency?->symbol_position,
                    ];
                })
            ]
        ]);
    }

    /**
     * Get all categories with product stats and latest products
     */
    public function categoriesWithStats(Request $request)
    {
        $categories = Category::where('status', 'active')
            ->withCount('products')
            ->with(['products' => function ($query) {
                $query->where('status', 'active')
                    ->latest()
                    ->limit(6);
            }])
            ->get();

        $result = $categories->map(function ($category) {
            $avgRating = Product::where('category_id', $category->id)
                ->where('status', 'active')
                ->join('ratings', 'products.id', '=', 'ratings.product_id')
                ->avg('ratings.rating');

            return [
                'id' => $category->id,
                'name' => $category->title,
                'image' => $category->image ? url($category->image) : null,
                'product_count' => $category->products_count,
                'average_rating' => $avgRating ? round($avgRating, 1) : 0,
                'latest_products' => ProductResource::collection($category->products),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $result,
        ]);
    }

    /**
     * Get all active coupons list
     */
    public function couponsList(Request $request)
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $coupons = Coupon::where('status', 'active')
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Coupons retrieved successfully',
            'data' => [
                'coupons' => $coupons->items(),
                'pagination' => [
                    'current_page' => $coupons->currentPage(),
                    'last_page' => $coupons->lastPage(),
                    'per_page' => $coupons->perPage(),
                    'total' => $coupons->total(),
                    'next_page_url' => $coupons->nextPageUrl(),
                    'prev_page_url' => $coupons->previousPageUrl(),
                ]
            ]
        ]);
    }

    /**
     * Autocomplete search for coupons
     */
    public function autocompleteCoupons(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $searchTerm = $request->q;
        $limit = $request->get('limit', 10);

        $coupons = Coupon::where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('title', 'LIKE', "%{$searchTerm}%");
                    });
            })
            ->with('category')
            ->limit($limit)
            ->get(['id', 'title', 'code', 'description', 'type', 'amount', 'percentage', 'start_from', 'end_on', 'category_id']);

        return response()->json([
            'success' => true,
            'message' => 'Coupon search results',
            'data' => [
                'search_term' => $searchTerm,
                'total_results' => $coupons->count(),
                'coupons' => $coupons->map(function ($coupon) {
                    return [
                        'id' => $coupon->id,
                        'title' => $coupon->title,
                        'code' => $coupon->code,
                        'description' => $coupon->description,
                        'type' => $coupon->type,
                        'amount' => $coupon->type == 'fixed' ? (float) $coupon->amount : null,
                        'percentage' => $coupon->type == 'percentage' ? (float) $coupon->percentage : null,
                        'start_from' => $coupon->start_from,
                        'end_on' => $coupon->end_on,
                        'is_valid' => $coupon->start_from <= now()->toDateString() && $coupon->end_on >= now()->toDateString(),
                        'category' => $coupon->category ? $coupon->category->title : null,
                    ];
                })
            ]
        ]);
    }

    /**
     * Get all active coupons list
     */
    public function codCharges(Request $request)
    {

        $codCharges = CodCharge::where('is_active', 1)
            ->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'COD charges retrieved successfully',
            'data' => $codCharges
        ]);
    }
}
