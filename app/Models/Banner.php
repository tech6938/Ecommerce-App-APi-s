<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'image',
        'status',
    ];

    public function getImageAttribute($value)
    {
        return $value
            ? asset($value)
            : null;
    }
}
