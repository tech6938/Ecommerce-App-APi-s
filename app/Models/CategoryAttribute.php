<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryAttribute extends Pivot
{
    protected $table = 'category_attribute';

    protected $fillable = [
        'category_id',
        'attribute_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
