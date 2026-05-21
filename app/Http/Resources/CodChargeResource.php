<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CodChargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'min_order_amount' => (float) $this->min_order_amount,
            'max_order_amount' => $this->max_order_amount ? (float) $this->max_order_amount : null,
            'charge_amount' => (float) $this->charge_amount,
            'charge_type' => $this->charge_type,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'formatted_charge' => $this->formatted_charge,
            'amount_range' => $this->amount_range,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
