<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RatingResource;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Get all ratings for a product
     */
    public function productRatings($productId)
    {
        $product = Product::findOrFail($productId);

        $ratings = $product->ratings()
            ->with('user')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Product ratings retrieved successfully',
            'data' => [
                'average_rating' => $product->average_rating,
                'total_reviews' => $product->total_reviews,
                'rating_distribution' => $product->rating_distribution,
                'ratings' => RatingResource::collection($ratings),
                // 'pagination' => [
                //     'current_page' => $ratings->currentPage(),
                //     'last_page' => $ratings->lastPage(),
                //     'per_page' => $ratings->perPage(),
                //     'total' => $ratings->total(),
                // ]
            ]
        ], 200);
    }

    /**
     * Add or update rating (post/update)
     */
    public function addOrUpdateRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $productId = $request->product_id;

        // Check if user already rated this product
        $existingRating = Rating::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingRating) {
            // Update existing rating
            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully',
                'data' => new RatingResource($existingRating->load('user'))
            ], 200);
        } else {
            // Create new rating
            $rating = Rating::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating added successfully',
                'data' => new RatingResource($rating->load('user'))
            ], 201);
        }
    }

    /**
     * Delete rating
     */
    public function deleteRating(Request $request, $id)
    {
        $user = $request->user();
        $rating = Rating::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating not found or you are not authorized to delete it'
            ], 404);
        }

        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating deleted successfully'
        ], 200);
    }

    /**
     * Get user's all ratings
     */
    public function myRatings(Request $request)
    {
        $user = $request->user();

        $ratings = $user->ratings()
            ->with('product')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Your ratings retrieved successfully',
            'data' => [
                'ratings' => $ratings->map(function($rating) {
                    return [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'product' => [
                            'id' => $rating->product->id,
                            'title' => $rating->product->title,
                            'thumbnail' => $rating->product->thumbnail,
                        ],
                        'created_at' => $rating->created_at->diffForHumans(),
                    ];
                }),
                // 'pagination' => [
                //     'current_page' => $ratings->currentPage(),
                //     'last_page' => $ratings->lastPage(),
                //     'per_page' => $ratings->perPage(),
                //     'total' => $ratings->total(),
                // ]
            ]
        ], 200);
    }

    /**
     * Get single rating details
     */
    public function showRating($id)
    {
        $rating = Rating::with(['user', 'product'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Rating details retrieved successfully',
            'data' => new RatingResource($rating)
        ], 200);
    }
}
