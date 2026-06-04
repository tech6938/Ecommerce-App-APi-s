@extends('layout.dashboard-layout')

@section('css')
<style>
    /* ── Layout ── */
    .chat-wrapper {
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 220px);
        min-height: 500px;
    }

    /* ── Top bar ── */
    .chat-topbar {
        padding: 14px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .chat-topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .chat-user-avatar {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 50%;
        background: #4e73df;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
    }
    .chat-user-name {
        font-size: 15px;
        font-weight: 600;
        color: #343a40;
        line-height: 1.2;
    }
    .chat-conv-id {
        font-size: 12px;
        color: #adb5bd;
    }
    .chat-topbar-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Status badge ── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .status-badge.pending { background: #fff3cd; color: #856404; }
    .status-badge.pending .dot { background: #ffc107; }
    .status-badge.active  { background: #d1e7dd; color: #0a5c36; }
    .status-badge.active .dot  { background: #198754; }
    .status-badge.closed  { background: #e2e3e5; color: #41464b; }
    .status-badge.closed .dot  { background: #6c757d; }

    /* ── Messages area ── */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 24px 20px;
        background: #f4f6f9;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Individual message row ── */
    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }
    .msg-row.right {
        flex-direction: row-reverse;
    }
    .msg-avatar {
        width: 30px;
        height: 30px;
        min-width: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }
    .msg-avatar.user-av  { background: #6c757d; }
    .msg-avatar.admin-av { background: #4e73df; }

    .msg-body {
        max-width: 65%;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .msg-row.right .msg-body { align-items: flex-end; }

    .msg-sender {
        font-size: 11px;
        font-weight: 600;
        color: #6c757d;
        padding: 0 4px;
    }

    .msg-bubble {
        padding: 10px 14px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.55;
        word-break: break-word;
    }
    .msg-row:not(.right) .msg-bubble {
        background: #fff;
        color: #343a40;
        border: 1px solid #dee2e6;
        border-bottom-left-radius: 4px;
    }
    .msg-row.right .msg-bubble {
        background: #4e73df;
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-row.right .msg-bubble .text-muted {
        color: rgba(255,255,255,.65) !important;
    }

    .msg-time {
        font-size: 11px;
        color: #adb5bd;
        padding: 0 4px;
    }

    /* ── Closed notice ── */
    .chat-closed-notice {
        padding: 12px 20px;
        background: #fff3cd;
        border-top: 1px solid #ffc107;
        text-align: center;
        font-size: 13px;
        color: #856404;
        flex-shrink: 0;
    }

    /* ── Input bar ── */
    .chat-input-bar {
        padding: 14px 20px;
        border-top: 1px solid #e3e6f0;
        background: #fff;
        flex-shrink: 0;
    }
    .chat-input-bar .input-group .form-control {
        border-radius: 22px 0 0 22px !important;
        border-right: none;
        font-size: 14px;
        padding: 10px 16px;
        height: auto;
    }
    .chat-input-bar .input-group .form-control:focus {
        box-shadow: none;
        border-color: #4e73df;
    }
    .chat-input-bar .input-group-append .btn {
        border-radius: 0 22px 22px 0 !important;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
    }
    .chat-input-hint {
        font-size: 11px;
        color: #adb5bd;
        margin-top: 6px;
        padding-left: 4px;
    }

    /* ── Scrollbar ── */
    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-track { background: transparent; }
    .chat-messages::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }
</style>
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            {{-- ── Page heading ── --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-light mr-2">
                        <i data-feather="arrow-left" width="14" height="14"></i>
                    </a>
                    <h5 class="mb-0">Support Chat</h5>
                </div>

                <span class="status-badge {{ $conversation->status }}">
                    <span class="dot"></span>
                    {{ ucfirst($conversation->status) }}
                </span>
            </div>

            {{-- ── Chat wrapper ── --}}
            <div class="chat-wrapper">

                {{-- Top bar --}}
                <div class="chat-topbar">
                    <div class="chat-topbar-left">
                        <div class="chat-user-avatar">
                            {{ strtoupper(substr($conversation->user->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <div class="chat-user-name">
                                {{ $conversation->user->name ?? 'Customer' }}
                            </div>
                            <div class="chat-conv-id">
                                #{{ $conversation->conversation_id }}
                                @if($conversation->user->email ?? false)
                                    · {{ $conversation->user->email }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="chat-topbar-actions">
                        @if($conversation->status != 'closed')
                            <form id="close-conversation-form"
                                  action="{{ route('admin.chat.close', $conversation->conversation_id) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                <button type="submit"
                                        id="close-conversation-button"
                                        class="btn btn-sm btn-outline-danger">
                                    <i data-feather="x-circle" width="13" height="13"></i>
                                    Close Conversation
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Messages --}}
                <div class="chat-messages" id="chat-messages">

                    @foreach($conversation->messages as $message)

                        <div class="msg-row {{ $message->isFromAdmin() ? 'right' : '' }}">

                            <div class="msg-avatar {{ $message->isFromAdmin() ? 'admin-av' : 'user-av' }}">
                                {{ strtoupper(substr($message->sender_name, 0, 2)) }}
                            </div>

                            <div class="msg-body">
                                <div class="msg-sender">{{ $message->sender_name }}</div>
                                <div class="msg-bubble">
                                    {{ $message->message }}
                                    @if($message->is_read)
                                        <small class="text-muted ml-2">
                                            <i data-feather="check" width="11" height="11"></i> Read
                                        </small>
                                    @endif
                                </div>
                                <div class="msg-time">
                                    {{ $message->created_at->format('H:i • d M Y') }}
                                </div>
                            </div>

                        </div>

                    @endforeach

                </div>

                {{-- Closed notice OR input bar --}}
                @if($conversation->status == 'closed')
                    <div class="chat-closed-notice">
                        <i data-feather="lock" width="13" height="13"></i>
                        This conversation is closed and no further replies can be sent.
                    </div>
                @else
                    <div class="chat-input-bar">
                        <form id="message-form"
                              action="{{ route('admin.chat.send', $conversation->conversation_id) }}"
                              method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text"
                                       name="message"
                                       id="message-input"
                                       class="form-control"
                                       placeholder="Type your message…"
                                       required
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i data-feather="send" width="14" height="14"></i>
                                        Send
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="chat-input-hint">Press Enter to send</div>
                    </div>
                @endif

            </div>{{-- /chat-wrapper --}}

        </div>
    </section>
</div>
@endsection

@section('js')
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    // Enable Pusher for real-time updates
    const pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
        wsHost: '{{ env('REVERB_HOST') }}',
        wsPort: {{ env('REVERB_PORT') }},
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws', 'wss']
    });

    const channel = pusher.subscribe('private-conversation.{{ $conversation->conversation_id }}');

    channel.bind('message.sent', function(data) {
        if (data.sender_type !== 'App\\Models\\Admin') {
            appendMessage({
                sender_name: data.sender_name,
                message:     data.message,
                isAdmin:     false,
                time:        'Just now',
                is_read:     false
            });
        }
    });

    // Auto scroll to bottom on load
    const chatContainer = document.getElementById('chat-messages');
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // Confirm close conversation with SweetAlert
    const closeForm = document.getElementById('close-conversation-form');
    if (closeForm) {
        closeForm.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to close this conversation?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    closeConversation();
                }
            });
        });
    }

    async function closeConversation() {
        const closeButton = document.getElementById('close-conversation-button');
        const formData = new FormData(closeForm);

        if (closeButton) {
            closeButton.disabled = true;
        }

        try {
            const response = await fetch(closeForm.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                window.location.href = result.redirect_url || "{{ route('admin.chat.index') }}";
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to close conversation. Please try again.'
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to close conversation. Please try again.'
            });
        } finally {
            if (closeButton) {
                closeButton.disabled = false;
            }
        }
    }

    // Handle form submission with AJAX
    document.getElementById('message-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form         = e.target;
        const formData     = new FormData(form);
        const messageInput = document.getElementById('message-input');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await response.json();

            if (result.success) {
                appendMessage({
                    sender_name: 'You (Admin)',
                    message:     result.message.message,
                    isAdmin:     true,
                    time:        'Just now',
                    is_read:     false
                });
                messageInput.value = '';
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });

    // Enter key to submit
    document.getElementById('message-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('message-form').dispatchEvent(new Event('submit'));
        }
    });

    // Helper: append a message bubble
    function appendMessage({ sender_name, message, isAdmin, time, is_read }) {
        const initials = sender_name.substring(0, 2).toUpperCase();
        const rowClass  = isAdmin ? 'msg-row right' : 'msg-row';
        const avClass   = isAdmin ? 'msg-avatar admin-av' : 'msg-avatar user-av';
        const readHtml  = is_read
            ? `<small class="text-muted ml-2"><i data-feather="check" width="11" height="11"></i> Read</small>`
            : '';

        const html = `
            <div class="${rowClass}">
                <div class="${avClass}">${initials}</div>
                <div class="msg-body">
                    <div class="msg-sender">${sender_name}</div>
                    <div class="msg-bubble">${message}${readHtml}</div>
                    <div class="msg-time">${time}</div>
                </div>
            </div>`;

        chatContainer.insertAdjacentHTML('beforeend', html);
        chatContainer.scrollTop = chatContainer.scrollHeight;

        if (typeof feather !== 'undefined') feather.replace();
    }

    if (typeof feather !== 'undefined') feather.replace();
</script>
@endsection
