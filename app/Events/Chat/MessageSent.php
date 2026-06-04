<?php

namespace App\Events\Chat;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->conversation = $message->conversation;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation->conversation_id),
            new PrivateChannel('user.' . $this->conversation->user_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->conversation->conversation_id,
            'message' => $this->message->message,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $this->message->sender_name,
            'sender_id' => $this->message->sender_id,
            'created_at' => $this->message->created_at->toISOString(),
            'is_read' => $this->message->is_read,
            'type' => $this->message->type,
            'attachment_url' => $this->message->attachment_url,
        ];
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
