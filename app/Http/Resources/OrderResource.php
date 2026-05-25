<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'order_number' => $this->order_number,
            'order_status' => $this->order_status,
            'status_badge' => $this->status_badge,
            'payment_status' => $this->payment_status,

            'amounts' => [
                'subtotal' => (float) $this->subtotal,
                'discount_amount' => (float) $this->discount_amount,
                'shipping_charge' => (float) $this->shipping_charge,
                'cod_charge' => (float) $this->cod_charge,
                'total_amount' => (float) $this->total_amount,
                'formatted_subtotal' => $this->formatted_subtotal,
                'formatted_total' => $this->formatted_total,
            ],

            'coupon' => $this->coupon_code ? [
                'code' => $this->coupon_code,
                'discount' => (float) $this->coupon_discount,
            ] : null,

            'payment' => [
                'method' => $this->paymentMethod ? [
                    'id' => (string) $this->paymentMethod->id,
                    'name' => $this->paymentMethod->name,
                    'code' => $this->paymentMethod->code,
                ] : null,
                'transaction_id' => $this->payment_transaction_id,
            ],

            'shipping' => [
                'address' => new AddressResource($this->whenLoaded('address')),
                'tracking_number' => $this->tracking_number,
                'carrier' => $this->shipping_carrier,
            ],

            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'customer_note' => $this->customer_note,
            'admin_note' => $this->admin_note,

            'dates' => [
                'created_at' => $this->created_at?->toISOString(),
                'created_at_human' => $this->created_at?->diffForHumans(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}
