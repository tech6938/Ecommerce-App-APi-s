<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get total number of items (sum of quantities)
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get total price of all cart items
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->line_total;
        });
    }

    /**
     * Get total discount amount
     */
    public function getTotalDiscountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->line_discount;
        });
    }

    /**
     * Get payable amount after discounts
     */
    public function getPayableAmountAttribute(): float
    {
        return $this->total_price - $this->total_discount;
    }
}
