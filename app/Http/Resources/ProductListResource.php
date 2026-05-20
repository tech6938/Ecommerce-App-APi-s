<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'brand' => $this->brand,
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail ? url($this->thumbnail) : null,
            'price' => [
                'original' => (float) $this->base_price,
                'currency' => $this->currency ?? '$',
            ],
            'rating' => [
                'average' => round($this->ratings()->avg('rating') ?? 0, 1),
                'count' => $this->ratings()->count(),
            ],
            'in_stock' => $this->variants->sum('stock_quantity') > 0,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
        ];
    }
}
