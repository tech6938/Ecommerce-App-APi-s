<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $fillable = [
        'attribute_id',
        'value',
        'hex_code',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants()
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_options',
            'attribute_option_id',
            'variant_id'
        )->withTimestamps();
    }

    public function taggedImages()
    {
        return $this->hasMany(ProductImage::class, 'attribute_option_id');
    }
}
