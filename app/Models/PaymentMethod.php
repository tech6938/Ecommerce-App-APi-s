<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'api_key',
        'secret_key',
        'merchant_key',
        'merchant_id',
        'public_key',
        'private_key',
        'callback_url',
        'webhook_secret',
        'environment',
        'extra_credentials',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'extra_credentials' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCod($query)
    {
        return $query->where('code', 'cod');
    }

    public function scopeOnline($query)
    {
        return $query->where('type', 'online');
    }

    public function isCod()
    {
        return $this->code === 'cod';
    }
}
