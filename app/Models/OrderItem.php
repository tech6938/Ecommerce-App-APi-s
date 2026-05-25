<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'unit_price',
        'discount_price',
        'total_price',
        'selected_options',
        'product_image',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'unit_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Accessors
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2) . ' FCFA';
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_price, 2) . ' FCFA';
    }
}
