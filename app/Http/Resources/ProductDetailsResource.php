<?php

namespace App\Http\Resources;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variants = $this->whenLoaded('variants', fn () => $this->variants, collect());
        $selectors = $this->buildSelectors($variants);
        $currency = Currency::current();

        return [
            'product' => [
                'id' => (string) $this->id,
                'name' => $this->title,
                'brand' => $this->brand,
                'description' => $this->description,
                'category' => $this->category ? [
                    'id' => (string) $this->category->id,
                    'name' => $this->category->title,
                ] : null,
                'currency' => $currency?->symbol ?: $currency?->currency_code ?: '$',
                'currency_position' => $currency?->symbol_position ?? 'before',
                'rating' => [
                    'average' => (float) $this->average_rating,
                    'count' => (int) $this->total_reviews,
                ],
                'images' => $this->buildImages(),
                'selectors' => $selectors->values()->all(),
                'variants' => $this->buildVariants($variants),
                'specs' => $this->buildSpecifications(),
                'likes_count' => (int) ($this->likes_count ?? 0),
                'is_liked' => (bool) optional($this->whenLoaded('userLike'))->is_like,
            ],
        ];
    }

    protected function buildSelectors(Collection $variants): Collection
    {
        return $variants
            ->flatMap(fn ($variant) => $variant->attributeOptions ?? collect())
            ->filter(fn ($option) => $option && $option->attribute)
            ->groupBy(fn ($option) => $option->attribute->id)
            ->map(function (Collection $options) {
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
            ->values();
    }

    protected function buildVariants(Collection $variants): array
    {
        if ($variants->isEmpty()) {
            return $this->buildFallbackVariant();
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
                }),
                'combination' => collect($variant->attributeOptions ?? [])
                    ->filter(fn ($option) => $option && $option->attribute)
                    ->mapWithKeys(fn ($option) => [
                        Str::slug($option->attribute->name, '_') => (string) $option->id,
                    ])
                    ->all(),
                'price' => [
                    'original' => $originalPrice,
                    'discounted' => $discountedPrice,
                    'discount_percent' => $this->calculateDiscountPercent($originalPrice, $discountedPrice),
                ],
                'in_stock' => (int) $variant->stock > 0,
                'stock_quantity' => (int) $variant->stock,
                'is_default' => (bool) $variant->is_default,
            ];
        })->values()->all();
    }

    protected function buildFallbackVariant(): array
    {
        $originalPrice = $this->resource->getAttribute('price');

        if ($originalPrice === null) {
            return [];
        }

        $originalPrice = (float) $originalPrice;
        $discountedPrice = $this->resource->getAttribute('discount_price') !== null
            ? (float) $this->resource->getAttribute('discount_price')
            : $originalPrice;

        return [[
            'id' => 'product_' . $this->id,
            'sku' => null,
            'combination' => new \stdClass(),
            'price' => [
                'original' => $originalPrice,
                'discounted' => $discountedPrice,
                'discount_percent' => $this->calculateDiscountPercent($originalPrice, $discountedPrice),
            ],
            'in_stock' => true,
            'stock_quantity' => null,
            'is_default' => true,
            'rating' => (float) $this->average_rating,
        ]];
    }

    protected function buildImages(): array
    {
        $images = $this->whenLoaded('images', fn () => $this->images, collect());

        if ($images->isNotEmpty()) {
            return $images->map(function ($image) {
                // $tags = [];

                if ($image->taggedOption && $image->taggedOption->attribute) {
                    $tags[Str::slug($image->taggedOption->attribute->name, '_') . '_id'] = (string) $image->taggedOption->id;
                }

                return [
                    'id' => (string) $image->id,
                    'url' => $image->url,
                    'is_primary' => (int) ($image->sort_order ?? 0) === 0,
                    // 'tags' => (object) $tags,
                ];
            })->values()->all();
        }

        if ($this->thumbnail) {
            return [[
                'id' => 'thumbnail_' . $this->id,
                'url' => $this->thumbnail,
                'is_primary' => true,
                'tags' => new \stdClass(),
            ]];
        }

        return [];
    }

    protected function buildSpecifications(): array
    {
        $specifications = $this->specifications;

        if (!is_array($specifications) || empty($specifications)) {
            return [];
        }

        return collect($specifications)
            ->map(function ($spec, $key) {
                if (is_array($spec) && isset($spec['label'], $spec['value'])) {
                    return [
                        'label' => $spec['label'],
                        'value' => $spec['value'],
                    ];
                }

                if (is_string($key)) {
                    return [
                        'label' => Str::headline($key),
                        'value' => is_scalar($spec) ? (string) $spec : json_encode($spec),
                    ];
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function calculateDiscountPercent(float $originalPrice, float $discountedPrice): float
    {
        if ($originalPrice <= 0 || $discountedPrice >= $originalPrice) {
            return 0.0;
        }

        return round((($originalPrice - $discountedPrice) / $originalPrice) * 100, 1);
    }
}
