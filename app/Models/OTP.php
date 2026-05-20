<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class OTP extends Model
{
    protected $table = 'otps';
    protected $fillable = [
        'email',
        'otp',
        'type',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if OTP is valid
     */
    public function isValid()
    {
        return !$this->is_used && now()->lessThan($this->expires_at);
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed()
    {
        $this->is_used = true;
        $this->save();
    }

    /**
     * Delete expired OTPs
     */
    public static function deleteExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }
}
