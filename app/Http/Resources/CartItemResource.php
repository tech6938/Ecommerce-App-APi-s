<?php

namespace App\Http\Resources;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->whenLoaded('product');
        $variant = $this->whenLoaded('variant');
        $currency = Currency::current();

        // Get prices
        $originalPrice = (float) $this->unit_price;
        $discountedPrice = (float) ($this->discount_price ?? $originalPrice);

        // Get all variants for this product (for variant switching)
        $allVariants = $product ? $this->buildAllVariants($product) : [];

        // Get selectors (for UI to show available options)
        $selectors = $product ? $this->buildSelectors($product) : [];

        return [
            'id' => (string) $this->id,
            'quantity' => (int) $this->quantity,

            // Product information (SAME as product details)
            'product' => $product ? [
                'id' => (string) $product->id,
                'name' => $product->title,
                'brand' => $product->brand,
                'description' => $product->description,
                'category' => $product->category ? [
                    'id' => (string) $product->category->id,
                    'name' => $product->category->title,
                ] : null,
                'currency' => $currency?->symbol ?: $currency?->currency_code ?: '$',
                'currency_position' => $currency?->symbol_position ?? 'before',
                'rating' => [
                    'average' => (float) ($product->average_rating ?? 0),
                    'count' => (int) ($product->total_reviews ?? 0),
                ],
                'images' => $product->thumbnail ? [
                    [
                        'id' => 'thumbnail_' . $product->id,
                        'url' => $product->thumbnail,
                        'is_primary' => true,
                        'tags' => (object) [],
                    ]
                ] : [],
                'thumbnail' => $product->thumbnail,
            ] : null,

            // Current selected variant
            'variant' => $variant ? [
                'id' => (string) $variant->id,
                'sku' => $variant->sku,
                'images' => $variant->images->map(function ($image) {
                    return [
                        'id' => (string) $image->id,
                        'url' => $image->image,
                    ];
                })->values()->all(),
                'combination' => $this->buildVariantCombination($variant),
                'price' => [
                    'original' => $originalPrice,
                    'discounted' => $discountedPrice,
                    'discount_percent' => $this->calculateDiscountPercent($originalPrice, $discountedPrice),
                ],
                'in_stock' => (int) $variant->stock > 0,
                'stock_quantity' => (int) $variant->stock,
                'is_default' => (bool) ($variant->is_default ?? false),
            ] : null,

            // ALL available variants for this product (for variant switching)
            'all_variants' => $allVariants,

            // Selectors for UI (same as product details)
            'selectors' => $selectors,

            // Selected options as selectors format
            'selected_options' => $this->buildSelectedOptionsAsSelectors($variant),

            // Price (SAME as product details variant price structure)
            'price' => [
                'original' => $originalPrice,
                'discounted' => $discountedPrice,
                'discount_percent' => $this->calculateDiscountPercent($originalPrice, $discountedPrice),
            ],

            // Line total for this cart item
            'line_total' => [
                'original' => number_format($originalPrice * $this->quantity, 2, '.', ''),
                'discounted' => number_format($discountedPrice * $this->quantity, 2, '.', ''),
                'saved' => number_format(($originalPrice - $discountedPrice) * $this->quantity, 2, '.', ''),
            ],

            'in_stock' => $variant ? ((int) $variant->stock > 0) : true,
        ];
    }

    /**
     * Build all variants for the product (same as product details)
     */
    protected function buildAllVariants($product): array
    {
        $variants = $product->variants ?? collect();

        if ($variants->isEmpty()) {
            return [];
        }

        return $variants->map(function ($variant) {
            $originalPrice = (float) $variant->price;
            $discountedPrice = $variant->discount_price !== null
                ? (float) $variant->discount_price
                : $originalPrice;

            return [
                'id' => (string) $variant->id,
                'sku' => $variant->sku,
                'images' => $variant->images->map(function ($image) {
                    return [
                        'id' => (string) $image->id,
                        'url' => $image->image,
                    ];
                })->values()->all(),
                'combination' => $this->buildVariantCombination($variant),
                'price' => [
                    'original' => $originalPrice,
                    'discounted' => $discountedPrice,
                    'discount_percent' => $this->calculateDiscountPercent($originalPrice, $discountedPrice),
                ],
                'in_stock' => (int) $variant->stock > 0,
                'stock_quantity' => (int) $variant->stock,
                'is_default' => (bool) ($variant->is_default ?? false),
            ];
        })->values()->all();
    }

    /**
     * Build selectors from product variants (same as product details)
     */
    protected function buildSelectors($product): array
    {
        $variants = $product->variants ?? collect();

        if ($variants->isEmpty()) {
            return [];
        }

        return $variants
            ->flatMap(fn ($variant) => $variant->attributeOptions ?? collect())
            ->filter(fn ($option) => $option && $option->attribute)
            ->groupBy(fn ($option) => $option->attribute->id)
            ->map(function ($options) {
                $attribute = $options->first()->attribute;

                return [
                    'key' => Str::slug($attribute->name, '_'),
                    'label' => $attribute->name,
                    'type' => $attribute->display_type ?: 'chip',
                    'options' => $options
                        ->unique('id')
                        ->values()
                        ->map(function ($option) {
                            $payload = [
                                'id' => (string) $option->id,
                                'label' => $option->value,
                            ];

                            if ($option->hex_code) {
                                $payload['hex'] = $option->hex_code;
                            }

                            return $payload;
                        })
                        ->all(),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * Build selected options exactly like product details selectors
     */
    protected function buildSelectedOptionsAsSelectors($variant): array
    {
        $selectedOptions = $this->selected_options ?? [];

        if (empty($selectedOptions) && !$variant) {
            return [];
        }

        // If we have variant with attribute options, build from there
        if ($variant && $variant->attributeOptions) {
            $selectors = [];

            foreach ($variant->attributeOptions as $option) {
                if (!$option || !$option->attribute) {
                    continue;
                }

                $attribute = $option->attribute;
                $selectors[] = [
                    'key' => Str::slug($attribute->name, '_'),
                    'label' => $attribute->name,
                    'type' => $attribute->display_type ?? 'chip',
                    'options' => [
                        [
                            'id' => (string) $option->id,
                            'label' => $option->value,
                        ]
                    ],
                ];
            }

            return $selectors;
        }

        // Fallback: build from selected_options JSON
        return collect($selectedOptions)->map(function ($value, $key) {
            return [
                'key' => Str::slug($key, '_'),
                'label' => ucfirst($key),
                'type' => 'chip',
                'options' => [
                    [
                        'id' => (string) $value,
                        'label' => is_string($value) ? ucfirst($value) : (string) $value,
                    ]
                ],
            ];
        })->values()->all();
    }

    /**
     * Build variant combination like in ProductDetailsResource
     */
    protected function buildVariantCombination($variant): array
    {
        $attributeOptions = $variant->attributeOptions ?? collect();

        return $attributeOptions
            ->filter(fn ($option) => $option && $option->attribute)
            ->mapWithKeys(fn ($option) => [
                Str::slug($option->attribute->name, '_') => (string) $option->id,
            ])
            ->all();
    }

    /**
     * Calculate discount percentage
     */
    protected function calculateDiscountPercent(float $originalPrice, float $discountedPrice): float
    {
        if ($originalPrice <= 0 || $discountedPrice >= $originalPrice) {
            return 0.0;
        }

        return round((($originalPrice - $discountedPrice) / $originalPrice) * 100, 1);
    }
}
