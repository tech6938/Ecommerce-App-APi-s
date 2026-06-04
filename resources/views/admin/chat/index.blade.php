@extends('layout.dashboard-layout')

@section('css')
<style>
    /* ── Stat cards ── */
    .chat-stat-card {
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow .2s;
    }
    .chat-stat-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
    }
    .chat-stat-icon {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chat-stat-icon.pending  { background: #fff3cd; color: #856404; }
    .chat-stat-icon.active   { background: #d1e7dd; color: #0a5c36; }
    .chat-stat-icon.closed   { background: #e2e3e5; color: #41464b; }
    .chat-stat-icon.total    { background: #cfe2ff; color: #084298; }
    .chat-stat-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 4px;
    }
    .chat-stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #343a40;
        line-height: 1;
    }

    /* ── Table card ── */
    .chat-table-card {
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .chat-table-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-table-card .card-header h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #343a40;
    }

    /* ── Conversation rows ── */
    .conv-table tbody tr {
        cursor: pointer;
        transition: background .15s;
    }
    .conv-table tbody tr:hover {
        background: #f8f9fa;
    }
    .conv-table td {
        vertical-align: middle !important;
        padding: 12px 16px !important;
        border-color: #f1f3f5 !important;
    }
    .conv-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6c757d;
        border-color: #e3e6f0 !important;
        padding: 12px 16px !important;
        background: #f8f9fa;
    }

    /* Avatar */
    .user-avatar {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        background: #4e73df;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        margin-right: 10px;
        vertical-align: middle;
    }
    .user-info {
        display: inline-block;
        vertical-align: middle;
    }
    .user-info .name {
        font-size: 14px;
        font-weight: 600;
        color: #343a40;
        line-height: 1.2;
    }
    .user-info .email {
        font-size: 12px;
        color: #6c757d;
    }

    /* Subject */
    .subject-cell {
        font-size: 13px;
        color: #343a40;
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Status badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .status-badge.pending  { background: #fff3cd; color: #856404; }
    .status-badge.pending .dot  { background: #ffc107; }
    .status-badge.active   { background: #d1e7dd; color: #0a5c36; }
    .status-badge.active .dot   { background: #198754; }
    .status-badge.closed   { background: #e2e3e5; color: #41464b; }
    .status-badge.closed .dot   { background: #6c757d; }

    /* Last message time */
    .time-cell {
        font-size: 13px;
        color: #6c757d;
        white-space: nowrap;
    }

    /* Search bar */
    .table-search {
        position: relative;
        width: 220px;
    }
    .table-search input {
        padding-left: 32px;
        font-size: 13px;
        border-radius: 20px;
        border: 1px solid #dee2e6;
        height: 34px;
    }
    .table-search .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        pointer-events: none;
    }

    /* Empty state */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: #adb5bd;
    }
    .empty-state svg {
        opacity: .3;
        margin-bottom: 12px;
    }
    .empty-state p {
        font-size: 14px;
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <x-sweet-alert />

            {{-- ── Stat cards ── --}}
            <div class="row mb-4">

                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="chat-stat-card">
                        <div class="chat-stat-icon pending">
                            <i data-feather="clock" width="22" height="22"></i>
                        </div>
                        <div>
                            <div class="chat-stat-label">Pending</div>
                            <div class="chat-stat-number">{{ $stats['pending'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="chat-stat-card">
                        <div class="chat-stat-icon active">
                            <i data-feather="message-circle" width="22" height="22"></i>
                        </div>
                        <div>
                            <div class="chat-stat-label">Active</div>
                            <div class="chat-stat-number">{{ $stats['active'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="chat-stat-card">
                        <div class="chat-stat-icon closed">
                            <i data-feather="check-circle" width="22" height="22"></i>
                        </div>
                        <div>
                            <div class="chat-stat-label">Closed</div>
                            <div class="chat-stat-number">{{ $stats['closed'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="chat-stat-card">
                        <div class="chat-stat-icon total">
                            <i data-feather="users" width="22" height="22"></i>
                        </div>
                        <div>
                            <div class="chat-stat-label">Total</div>
                            <div class="chat-stat-number">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Conversations table ── --}}
            <div class="chat-table-card">

                <div class="card-header">
                    <h4>Support Conversations</h4>
                    <div class="table-search">
                        <i data-feather="search" width="14" height="14" class="search-icon"></i>
                        <input type="text" id="tableSearch" placeholder="Search…" class="form-control">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table conv-table mb-0" id="convTable">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Last Message</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($conversations as $conversation)
                            <tr onclick="window.location='{{ route('admin.chat.show', $conversation->conversation_id) }}'">

                                <td style="font-size:13px; color:#6c757d; width:50px;">
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($conversation->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="user-info">
                                        <div class="name">{{ $conversation->user->name ?? 'N/A' }}</div>
                                        <div class="email">{{ $conversation->user->email ?? '' }}</div>
                                    </div>
                                </td>

                                <td>
                                    <div class="subject-cell" title="{{ $conversation->subject }}">
                                        {{ $conversation->subject }}
                                    </div>
                                </td>

                                <td>
                                    @if($conversation->status == 'pending')
                                        <span class="status-badge pending">
                                            <span class="dot"></span> Pending
                                        </span>
                                    @elseif($conversation->status == 'active')
                                        <span class="status-badge active">
                                            <span class="dot"></span> Active
                                        </span>
                                    @else
                                        <span class="status-badge closed">
                                            <span class="dot"></span> Closed
                                        </span>
                                    @endif
                                </td>

                                <td class="time-cell">
                                    {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'No messages' }}
                                </td>

                                <td onclick="event.stopPropagation()">
                                    <a href="{{ route('admin.chat.show', $conversation->conversation_id) }}"
                                       class="btn btn-sm btn-primary">
                                        <i data-feather="message-square" width="13" height="13"></i>
                                        View Chat
                                    </a>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i data-feather="inbox" width="48" height="48"></i>
                                        <p>No conversations found.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

                @if($conversations->hasPages())
                <div class="card-footer bg-white border-top-0 pt-3">
                    {{ $conversations->links() }}
                </div>
                @endif

            </div>

        </div>
    </section>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof feather !== 'undefined') feather.replace();

    // Live search filter
    const searchInput = document.getElementById('tableSearch');
    const rows = document.querySelectorAll('#convTable tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            rows.forEach(function (row) {
                if (row.querySelector('.empty-state')) return;
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

});
</script>
@endsection
