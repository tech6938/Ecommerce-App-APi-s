<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Effective unit price (variant price > product price)
     */
    public function getUnitPriceAttribute(): float
    {
        if ($this->variant) {
            return (float) $this->variant->price;
        }
        return (float) $this->product->price;
    }

    /**
     * Effective discount price
     */
    public function getUnitDiscountPriceAttribute(): ?float
    {
        if ($this->variant) {
            return $this->variant->discount_price
                ? (float) $this->variant->discount_price
                : null;
        }
        return $this->product->discount_price
            ? (float) $this->product->discount_price
            : null;
    }

    /**
     * Line total (before discount)
     */
    public function getLineTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * How much is saved on this line
     */
    public function getLineDiscountAttribute(): float
    {
        if ($this->unit_discount_price !== null) {
            return ($this->unit_price - $this->unit_discount_price) * $this->quantity;
        }
        return 0.0;
    }

    /**
     * What customer actually pays for this line
     */
    public function getLinePayableAttribute(): float
    {
        return $this->line_total - $this->line_discount;
    }
}
