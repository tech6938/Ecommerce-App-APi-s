<?php

namespace App\Events\Chat;

use App\Models\Conversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable;

    public $conversation;
    public $readBy;
    public $messageIds;

    public function __construct(Conversation $conversation, $readBy, array $messageIds)
    {
        $this->conversation = $conversation;
        $this->readBy = $readBy;
        $this->messageIds = $messageIds;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversation->conversation_id);
    }

    public function broadcastAs()
    {
        return 'message.read';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversation->conversation_id,
            'read_by' => $this->readBy,
            'message_ids' => $this->messageIds,
        ];
    }
}
