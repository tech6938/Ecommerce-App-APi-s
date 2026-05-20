<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantImage extends Model
{
    protected $fillable = [
        'variant_id',
        'image',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getImageAttribute($value)
    {
        return $value
            ? asset($value)
            : null;
    }
}
