<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'title',
        'image',
        'status',
    ];

    public function getImageAttribute($value)
    {
        return $value
            ? asset($value)
            : null;
    }

    public function attributes()
    {
        return $this->belongsToMany(
            Attribute::class,
            'category_attribute',
            'category_id',
            'attribute_id'
        )->withTimestamps()->orderBy('attributes.id');
    }

    public function categoryAttributes()
    {
        return $this->hasMany(CategoryAttribute::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
