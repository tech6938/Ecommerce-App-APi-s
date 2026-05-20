<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [

        'category_id',
        'title',
        'description',
        'type',
        'amount',
        'percentage',
        'start_from',
        'end_on',
        'code',
        'status',

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}