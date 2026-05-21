<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $hidden = ['created_at'];
    protected $fillable = [
        'user_id',
        'region',
        'city',
        'details',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($address) {
            if (self::where('user_id', $address->user_id)->count() === 0) {
                $address->is_default = true;
            }
        });

        static::updating(function ($address) {
            if ($address->is_default && $address->getOriginal('is_default') !== true) {
                self::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
