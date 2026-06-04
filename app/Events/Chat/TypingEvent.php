<?php

namespace App\Events\Chat;

use App\Models\Conversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TypingEvent implements ShouldBroadcast
{
    use Dispatchable;

    public $conversation;
    public $senderType;
    public $isTyping;

    public function __construct(Conversation $conversation, $senderType, $isTyping = true)
    {
        $this->conversation = $conversation;
        $this->senderType = $senderType;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversation->conversation_id);
    }

    public function broadcastAs()
    {
        return 'typing';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversation->conversation_id,
            'sender_type' => $this->senderType,
            'is_typing' => $this->isTyping,
        ];
    }
}
