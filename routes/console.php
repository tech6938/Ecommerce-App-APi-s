<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Conversation;
use App\Models\Admin;
use Illuminate\Support\Facades\Broadcast;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Authenticate conversation channel
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Handle different guard types
    if (!$user) {
        return false;
    }

    $conversation = Conversation::where('conversation_id', $conversationId)->first();

    if (!$conversation) {
        return false;
    }

    // Check if user is the customer (User model)
    if (get_class($user) === 'App\Models\User') {
        return $conversation->user_id === $user->id;
    }

    // Check if user is admin (Admin model)
    if (get_class($user) === 'App\Models\Admin') {
        return $conversation->admin_id === $user->id || $conversation->status === 'pending';
    }

    return false;
});

// User specific channel for notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    if (get_class($user) === 'App\Models\User') {
        return $user->id === (int) $userId;
    }
    return false;
});
