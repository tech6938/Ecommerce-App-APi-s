<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->variant;
        $product = $this->product;

        return [
            'id'         => $this->id,
            'quantity'   => $this->quantity,

            'product' => [
                'id'        => $product->id,
                'title'     => $product->title,
                'brand'     => $product->brand,
                'thumbnail' => $product->thumbnail,
                'status'    => $product->status,
            ],

            'variant' => $variant ? [
                'id'            => $variant->id,
                'sku'           => $variant->sku,
                'stock'         => $variant->stock,
                'is_default'    => $variant->is_default,
                'attribute_options' => $variant->attributeOptions->map(fn($opt) => [
                    'attribute_id'   => $opt->attribute_id,
                    'attribute_name' => $opt->attribute->name ?? null,
                    'option_id'      => $opt->id,
                    'option_value'   => $opt->value,
                ]),
                'images' => $variant->images->map(fn($img) => [
                    'id'    => $img->id,
                    'image' => asset($img->image),
                ]),
            ] : null,

            'pricing' => [
                'unit_price'          => number_format($this->unit_price, 2, '.', ''),
                'unit_discount_price' => $this->unit_discount_price
                    ? number_format($this->unit_discount_price, 2, '.', '')
                    : null,
                'line_total'    => number_format($this->line_total, 2, '.', ''),
                'line_discount' => number_format($this->line_discount, 2, '.', ''),
                'line_payable'  => number_format($this->line_payable, 2, '.', ''),
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
