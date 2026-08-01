<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'currency_name',
        'currency_code',
        'symbol',
        'symbol_position',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public static function current(): ?self
    {
        static $currentCurrency = null;

        if ($currentCurrency instanceof self) {
            return $currentCurrency;
        }

        $currentCurrency = static::query()
            ->active()
            ->latest('id')
            ->first()
            ?? static::query()->latest('id')->first();

        return $currentCurrency;
    }

    /**
     * Format amount with currency symbol based on position setting
     *
     * @param float|int|string $amount
     * @param bool $includeSpace Add space between symbol and amount
     * @return string
     */
    public function format($amount, bool $includeSpace = true): string
    {
        $symbol = $this->symbol ?: $this->currency_code ?: '$';
        $formattedAmount = number_format((float) $amount, 2, '.', '');
        $space = $includeSpace ? ' ' : '';

        if ($this->symbol_position === 'after') {
            return $formattedAmount . $space . $symbol;
        }

        return $symbol . $space . $formattedAmount;
    }

    /**
     * Get just the symbol/code without formatting
     *
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol ?: $this->currency_code ?: '$';
    }
}
