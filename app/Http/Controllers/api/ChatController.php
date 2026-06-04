<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Admin;
use App\Events\Chat\MessageSent;
use App\Events\Chat\TypingEvent;
use App\Events\Chat\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Start a new conversation (Customer)
     */
    public function startConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('sanctum')->user();

        // Check for existing open conversation
        $existingConversation = Conversation::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($existingConversation) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active conversation',
                'conversation' => $existingConversation,
                'conversation_id' => $existingConversation->conversation_id
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create conversation
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'subject' => $request->subject ?? 'General Inquiry',
                'status' => 'pending',
                'last_message_at' => now(),
            ]);

            // Create first message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => User::class,
                'sender_id' => $user->id,
                'message' => $request->message,
                'type' => 'text',
            ]);

            DB::commit();

            // Broadcast event (notify admins about new message)
            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Conversation started successfully',
                'conversation' => $conversation,
                'conversation_id' => $conversation->conversation_id,
                'first_message' => $message
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to start conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all conversations for admin
     */
    public function getConversations(Request $request)
    {
        $user = auth('sanctum')->user();
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');

        $query = Conversation::with(['user', 'admin', 'lastMessage']);

        // If regular user, only show their conversations
        if (get_class($user) === User::class) {
            $query->where('user_id', $user->id);
        }
        // If admin, show all or filter by status
        else if (get_class($user) === Admin::class) {
            if ($status && in_array($status, ['pending', 'active', 'closed'])) {
                $query->where('status', $status);
            }

            // Add unread count for admin
            $query->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_type', User::class)
                    ->where('is_read', false);
            }]);
        }

        $conversations = $query->orderBy('last_message_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(Request $request, $conversationId)
    {
        $user = auth('sanctum')->user();
        $conversation = Conversation::where('conversation_id', $conversationId)->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        // Authorize access
        $isAuthorized = false;

        if (get_class($user) === User::class && $conversation->user_id === $user->id) {
            $isAuthorized = true;
        } else if (get_class($user) === Admin::class) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $perPage = $request->get('per_page', 50);
        $messages = Message::with('sender')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        // Mark messages as read if admin is viewing
        if (get_class($user) === Admin::class) {
            $unreadMessages = Message::where('conversation_id', $conversation->id)
                ->where('sender_type', User::class)
                ->where('is_read', false)
                ->get();

            $messageIds = [];
            foreach ($unreadMessages as $message) {
                $message->markAsRead();
                $messageIds[] = $message->id;
            }

            if (count($messageIds) > 0) {
                broadcast(new MessageRead($conversation, 'admin', $messageIds))->toOthers();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $messages,
            'conversation' => $conversation
        ]);
    }

    /**
     * Send a message (Both Customer and Admin)
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('sanctum')->user();
        $conversation = Conversation::where('conversation_id', $conversationId)->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        // Determine sender type and authorize
        $senderType = null;
        $senderId = null;

        if (get_class($user) === User::class) {
            if ($conversation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            $senderType = User::class;
            $senderId = $user->id;
        } else if (get_class($user) === Admin::class) {
            // If admin and conversation is pending, assign this admin
            if ($conversation->status === 'pending') {
                $conversation->assignAdmin($user->id);
            }

            // Verify admin is assigned to this conversation
            if ($conversation->admin_id && $conversation->admin_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This conversation is handled by another admin'
                ], 403);
            }

            $senderType = Admin::class;
            $senderId = $user->id;
        }

        DB::beginTransaction();
        try {
            // Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'message' => $request->message,
                'type' => $request->type ?? 'text',
                'attachment_url' => $request->attachment_url ?? null,
            ]);

            // Update conversation timestamp
            $conversation->update(['last_message_at' => now()]);

            DB::commit();

            // Broadcast the message
            broadcast(new MessageSent($message));

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message->load('sender')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send typing indicator
     */
    public function sendTyping(Request $request, $conversationId)
    {
        $user = auth('sanctum')->user();
        $conversation = Conversation::where('conversation_id', $conversationId)->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        $senderType = get_class($user) === User::class ? 'customer' : 'admin';

        broadcast(new TypingEvent($conversation, $senderType, $request->input('is_typing', true)));

        return response()->json(['success' => true]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $conversationId)
    {
        $user = auth('sanctum')->user();
        $conversation = Conversation::where('conversation_id', $conversationId)->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        $senderType = get_class($user) === User::class ? Admin::class : User::class;

        $messages = Message::where('conversation_id', $conversation->id)
            ->where('sender_type', $senderType)
            ->where('is_read', false)
            ->get();

        $messageIds = [];
        foreach ($messages as $message) {
            $message->markAsRead();
            $messageIds[] = $message->id;
        }

        if (count($messageIds) > 0) {
            $readBy = get_class($user) === User::class ? 'customer' : 'admin';
            broadcast(new MessageRead($conversation, $readBy, $messageIds))->toOthers();
        }

        return response()->json([
            'success' => true,
            'marked_count' => count($messageIds)
        ]);
    }

    /**
     * Close conversation (Admin only)
     */
    public function closeConversation($conversationId)
    {
        $admin = auth('sanctum')->user();

        if (get_class($admin) !== Admin::class) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can close conversations'
            ], 403);
        }

        $conversation = Conversation::where('conversation_id', $conversationId)->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        $conversation->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation closed successfully'
        ]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount()
    {
        $user = auth('sanctum')->user();

        if (get_class($user) === User::class) {
            $unreadCount = Message::whereHas('conversation', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->where('sender_type', Admin::class)
                ->where('is_read', false)
                ->count();
        } else {
            $unreadCount = Message::whereHas('conversation', function ($q) {
                $q->where('status', 'active');
            })
                ->where('sender_type', User::class)
                ->where('is_read', false)
                ->count();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }
}
