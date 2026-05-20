<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'status' => $this->status,
            'price' => $this->price ?? '0.0',
            'discount_price' => $this->discount_price ?? '0.0',
            'currency' => $this->currency ?? '$',

            // Rating information
            'rating' => (float) $this->average_rating,
            'rating_count' => (int) $this->ratings_count,
            'total_reviews' => (int) $this->total_reviews,
            // 'likes_count' => (int) ($this->likes_count ?? 0),
            'is_liked' => (bool) optional($this->whenLoaded('userLike'))->is_like,
        ];
    }
}
