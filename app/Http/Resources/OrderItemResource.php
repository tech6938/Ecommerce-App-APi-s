<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'product_id' => (string) $this->product_id,
            'product_name' => $this->product_name,
            'product_image' => $this->product_image ? url($this->product_image) : null,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount_price' => (float) $this->discount_price,
            'total_price' => (float) $this->total_price,
            'formatted_unit_price' => $this->formatted_unit_price,
            'formatted_total' => $this->formatted_total,
            'variant' => $this->variant ? [
                'id' => (string) $this->variant->id,
                'name' => $this->variant_name,
                'sku' => $this->variant->sku,
            ] : null,
            'selected_options' => $this->selected_options,
        ];
    }
}
