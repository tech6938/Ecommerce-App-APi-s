<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image',
        'sort_order',
        'attribute_option_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function taggedOption()
    {
        return $this->belongsTo(AttributeOption::class, 'attribute_option_id');
    }

    // Full URL helper
    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return asset($this->image);
    }
}
