<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodCharge extends Model
{
    protected $table = 'cod_charges';

    protected $fillable = [
        'min_order_amount',
        'max_order_amount',
        'charge_amount',
        'charge_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scope for active charges
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for order amount range
    public function scopeForAmount($query, $amount)
    {
        return $query->where('min_order_amount', '<=', $amount)
            ->where(function($q) use ($amount) {
                $q->where('max_order_amount', '>=', $amount)
                  ->orWhereNull('max_order_amount');
            });
    }

    // Calculate charge for given order amount
    public static function calculateCharge($orderAmount)
    {
        $chargeRule = self::active()
            ->forAmount($orderAmount)
            ->orderBy('sort_order')
            ->first();

        if (!$chargeRule) {
            return 0;
        }

        if ($chargeRule->charge_type === 'percentage') {
            return ($orderAmount * $chargeRule->charge_amount) / 100;
        }

        return $chargeRule->charge_amount;
    }

    // Get formatted charge
    public function getFormattedChargeAttribute()
    {
        if ($this->charge_type === 'percentage') {
            return $this->charge_amount . '%';
        }
        return number_format($this->charge_amount, 2) . ' FCFA';
    }

    // Get amount range display
    public function getAmountRangeAttribute()
    {
        $min = number_format($this->min_order_amount, 2);
        if ($this->max_order_amount) {
            $max = number_format($this->max_order_amount, 2);
            return "{$min} - {$max} FCFA";
        }
        return "≥ {$min} FCFA";
    }
}
