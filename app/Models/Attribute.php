<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'display_type',
    ];

    public function options()
    {
        return $this->hasMany(AttributeOption::class)->orderBy('id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attribute')->withTimestamps();
    }

    public function categoryLinks()
    {
        return $this->hasMany(CategoryAttribute::class);
    }
}
