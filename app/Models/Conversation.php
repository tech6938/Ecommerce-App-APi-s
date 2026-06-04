<?php

namespace App\Models;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'conversations';

    protected $fillable = [
        'conversation_id', 'user_id', 'admin_id', 'status',
        'subject', 'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            if (empty($conversation->conversation_id)) {
                $conversation->conversation_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Helper methods
    public function getUnreadCountForAdmin()
    {
        return $this->messages()
            ->where('sender_type', User::class)
            ->where('is_read', false)
            ->count();
    }

    public function getUnreadCountForUser($userId)
    {
        return $this->messages()
            ->where('sender_type', Admin::class)
            ->where('is_read', false)
            ->count();
    }

    public function markMessagesAsRead($senderType)
    {
        $this->messages()
            ->where('sender_type', $senderType)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    public function assignAdmin($adminId)
    {
        $this->update([
            'admin_id' => $adminId,
            'status' => 'active'
        ]);
    }
}
