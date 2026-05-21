<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cart' => [
                'id' => (string) $this->id,
                'user_id' => (string) $this->user_id,

                'items' => CartItemResource::collection($this->whenLoaded('items')),

                'summary' => [
                    'total_items' => (int) $this->total_quantity,      // sum of all quantities
                    'unique_products' => (int) $this->items->count(),  // distinct line items
                    'subtotal' => number_format($this->total_price, 2, '.', ''),
                    'total_discount' => number_format($this->total_discount, 2, '.', ''),
                    'payable_amount' => number_format($this->payable_amount, 2, '.', ''),
                ],

                'updated_at' => $this->updated_at?->toISOString(),
            ]
        ];
    }
}
