<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'user_id' => $this->user_id,

            'items' => CartItemResource::collection($this->items),

            'summary' => [
                'total_items'    => $this->items->count(),       // distinct line items
                'total_quantity' => $this->total_quantity,       // sum of all quantities
                'subtotal'       => number_format($this->total_price, 2, '.', ''),
                'total_discount' => number_format($this->total_discount, 2, '.', ''),
                'payable_amount' => number_format($this->payable_amount, 2, '.', ''),
            ],

            'updated_at' => $this->updated_at,
        ];
    }
}
