<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'brand',
        'description',
        'thumbnail',
        'status',
        'price',
        'currency',
        'discount_price',
        'specifications',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'specifications' => 'array',
    ];

    public function getThumbnailAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://'])
            ? $value
            : asset($value);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    // Pehli/main image
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort_order');
    }

    public function likes()
    {
        return $this->hasMany(ProductLike::class);
    }

    public function userLike()
    {
        return $this->hasOne(ProductLike::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    // Calculate average rating
    public function getAverageRatingAttribute()
    {
        if (array_key_exists('ratings_avg_rating', $this->attributes)) {
            $average = $this->attributes['ratings_avg_rating'];
            return $average !== null ? number_format((float) $average, 1, '.', '') : '0.0';
        }

        $average = $this->ratings()->avg('rating');
        return $average ? number_format((float) $average, 1, '.', '') : '0.0';
    }

    // Get total reviews count
    public function getTotalReviewsAttribute()
    {
        if (array_key_exists('ratings_count', $this->attributes)) {
            return (int) $this->attributes['ratings_count'];
        }

        return $this->ratings()->count();
    }

    // Get rating distribution (5 stars, 4 stars, etc)
    public function getRatingDistributionAttribute()
    {
        $distribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];

        $ratings = $this->ratings()
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->get();

        foreach ($ratings as $rating) {
            $distribution[$rating->rating] = $rating->total;
        }

        return $distribution;
    }

    // Check if user has rated this product
    public function userRating($userId)
    {
        return $this->ratings()->where('user_id', $userId)->first();
    }
}
