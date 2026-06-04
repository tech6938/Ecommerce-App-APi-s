<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\Chat\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['user', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        $stats = [
            'pending' => Conversation::where('status', 'pending')->count(),
            'active' => Conversation::where('status', 'active')->count(),
            'closed' => Conversation::where('status', 'closed')->count(),
            'total' => Conversation::count(),
        ];

        return view('admin.chat.index', compact('conversations', 'stats'));
    }

    public function show($conversationId)
    {
        $conversation = Conversation::where('conversation_id', $conversationId)
            ->with(['user', 'messages.sender'])
            ->firstOrFail();

        // Mark messages as read
        $conversation->messages()
            ->where('sender_type', 'App\Models\User')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('admin.chat.show', compact('conversation'));
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $conversation = Conversation::where('conversation_id', $conversationId)->firstOrFail();
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            return redirect()->route('login');
        }

        // Assign admin if not assigned
        if (!$conversation->admin_id) {
            $conversation->assignAdmin($admin->id);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => get_class($admin),
            'sender_id' => $admin->id,
            'message' => $request->message,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new MessageSent($message));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message->load('sender')]);
        }

        return redirect()->back()->with('success', 'Message sent successfully');
    }

    public function close(Request $request, $conversationId)
    {
        try {
            $conversation = Conversation::where('conversation_id', $conversationId)->firstOrFail();

            if (!$conversation->update(['status' => 'closed'])) {
                throw new RuntimeException('Conversation status update failed.');
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conversation closed',
                    'redirect_url' => route('admin.chat.index'),
                ]);
            }

            return redirect()->route('admin.chat.index')->with('success', 'Conversation closed');
        } catch (Throwable $e) {
            Log::error('Failed to close conversation.', [
                'conversation_id' => $conversationId,
                'exception' => $e,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to close conversation. Please try again.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to close conversation. Please try again.');
        }
    }
}
