<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'discount_price',
        'stock',
        'image',
        'is_default',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock' => 'integer',
        'is_default' => 'boolean',
    ];

    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://'])
            ? $value
            : asset($value);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantOptions()
    {
        return $this->hasMany(ProductVariantOption::class, 'variant_id');
    }

    public function options()
    {
        return $this->variantOptions();
    }

    public function attributeOptions()
    {
        return $this->belongsToMany(
            AttributeOption::class,
            'product_variant_options',
            'variant_id',
            'attribute_option_id'
        )->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class, 'variant_id');
    }
}
