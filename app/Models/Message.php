<?php

namespace App\Models;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'conversation_id', 'sender_type', 'sender_id', 'message',
        'type', 'attachment_url', 'is_read', 'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Polymorphic relationship
    public function sender()
    {
        return $this->morphTo();
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Helper methods
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function isFromUser()
    {
        return $this->sender_type === User::class;
    }

    public function isFromAdmin()
    {
        return $this->sender_type === Admin::class;
    }

    // Accessor for sender name
    public function getSenderNameAttribute()
    {
        if ($this->isFromUser()) {
            return $this->sender->name ?? 'User';
        }
        return $this->sender->email ?? 'Admin';
    }
}
