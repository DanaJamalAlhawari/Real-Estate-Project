@extends('layouts.app')

@section('title', 'Makan — Messages')
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="chat"@endsection

@section('content')
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input type="checkbox" class="sidebar-toggle" id="sidebarToggle" aria-label="Collapse the menu" />

    <!-- ===== Topbar ===== -->
    <header class="app-topbar">
        <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar"
            aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <label for="sidebarToggle" class="icon-btn sidebar-burger d-none d-lg-inline-flex"
            title="Collapse or expand the menu" aria-hidden="true">
            <i class="bi bi-list"></i>
        </label>
        <a class="brand" href="{{ route('owner.dashboard') }}"><span class="brand-mark">M</span><span>Makan</span></a>
        <div class="ms-auto d-flex align-items-center gap-1">
            <!-- Messages: icon with unread count -->
            <a class="icon-btn is-active" href="{{ route('owner.chat') }}" aria-label="Messages, 1 unread"
                aria-current="page">
                <i class="bi bi-chat-dots"></i><span class="icon-badge">1</span>
            </a>
            <div class="dropdown">
                <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Notifications">
                    <i class="bi bi-bell"></i><span class="dot"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 320px">
                    <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
                    <div class="py-1">
                        <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ route('owner.requests') }}">
                            <span class="kpi-icon flex-shrink-0" style="width: 34px; height: 34px; font-size: 1rem"><i
                                    class="bi bi-inbox"></i></span>
                            <span class="small">
                                <span class="d-block">New booking request from Sara Khaled</span>
                                <span class="text-muted" style="font-size: 0.72rem">10 minutes ago</span>
                            </span>
                        </a>
                        <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ route('owner.properties') }}">
                            <span class="kpi-icon flex-shrink-0" style="width: 34px; height: 34px; font-size: 1rem"><i
                                    class="bi bi-shield-check"></i></span>
                            <span class="small">
                                <span class="d-block">Your Al Jubeiha studio has been verified</span>
                                <span class="text-muted" style="font-size: 0.72rem">1 hour ago</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <button class="app-user app-user--avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="Account menu, Abu Ahmad">
                    <span class="avatar">A</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <h6 class="dropdown-header">Abu Ahmad</h6>
                    </li>
                    <li><a class="dropdown-item" href="{{ route('owner.account') }}">My account</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <x-logout-form />
                    </li>
                </ul>
            </div>
        </div>
        <div class="dropdown">
          <button
            class="app-user app-user--avatar"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Account menu, {{ auth()->user()->display_name }}"
          >
            <span class="avatar">{{ auth()->user()->display_initial }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><h6 class="dropdown-header">{{ auth()->user()->display_name }}</h6></li>
            <li><a class="dropdown-item" href="{{ route('owner.account') }}">My account</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <x-logout-form />
            </li>
          </ul>
        </div>
      </div>
    </header>

    <div class="app-shell">
        <!-- ===== Sidebar ===== -->
        <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar">
            <div class="offcanvas-header border-bottom">
                <a class="brand" href="{{ route('home') }}"><span class="brand-mark">M</span><span>Makan</span></a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="side-label">Property owner</div>
                <a class="side-link" href="{{ route('owner.dashboard') }}" title="Dashboard"><i
                        class="bi bi-grid-1x2"></i><span class="flex-grow-1">Dashboard</span></a>
                <a class="side-link" href="{{ route('owner.properties') }}" title="My properties"><i
                        class="bi bi-house-door"></i><span class="flex-grow-1">My properties</span></a>
                <a class="side-link" href="{{ route('owner.property-form') }}" title="Add property"><i
                        class="bi bi-plus-square"></i><span class="flex-grow-1">Add property</span></a>
                <a class="side-link" href="{{ route('owner.requests') }}" title="Booking requests"><i
                        class="bi bi-inbox"></i><span class="flex-grow-1">Booking requests</span><span
                        class="side-badge">3</span></a>
                <a class="side-link active" href="{{ route('owner.chat') }}" aria-current="page" title="Messages"><i
                        class="bi bi-chat-dots"></i><span class="flex-grow-1">Messages</span><span
                        class="side-badge">1</span></a>
                <a class="side-link" href="{{ route('owner.account') }}" title="My account"><i
                        class="bi bi-person-badge"></i><span class="flex-grow-1">My account</span></a>
            </div>
        </aside>

        <!-- ===== Content ===== -->
        <main class="app-main">
            <!-- 1. Page header -->
            <h1 class="h3 mb-3">Messages</h1>

            <!-- 2. Chat -->
            <div class="chat">
                <!-- Conversation list -->
                <div class="chat-list" id="conversationList">
                    <div class="chat-list-head">
                        <div class="search-input">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search" class="form-control" id="chatSearch" name="q"
                                placeholder="Search conversations..." aria-label="Search conversations" />
                        </div>
                    </div>

                    @forelse ($conversations as $chat)
                        @php($isActive = isset($conversation) && $conversation->is($chat))

                        <a href="{{ route('conversations.show', ['conversation' => $chat]) }}"
                            @class(['chat-item', 'active' => $isActive]) @if ($isActive) aria-current="page" @endif>
                            <span class="avatar avatar-lg">
                                <i class="bi bi-chat-dots" aria-hidden="true"></i>
                            </span>

                            <span class="meta">
                                <b>Conversation #{{ $chat->id }}</b>

                                @if ($chat->last_message_at)
                                    <time>{{ \Illuminate\Support\Carbon::parse($chat->last_message_at)->format('d M, g:i A') }}</time>
                                @endif
                            </span>
                            <span class="unread" data-unread-for="{{ $chat->id }}"
                                aria-label="{{ $chat->unread_count }} unread messages"
                                @if ($chat->unread_count == 0) hidden @endif>
                                {{ $chat->unread_count }}
                            </span>
                        </a>
                    @empty
                        <p class="text-muted px-3 py-2 mb-0">No conversations yet.</p>
                    @endforelse
                </div>

                @isset($conversation)
                    <div class="chat-thread" id="chatThread">
                        <div class="chat-head">
                            <span class="avatar avatar-lg">
                                {{ $tenantName !== '' ? mb_strtoupper(mb_substr($tenantName, 0, 1)) : 'T' }}
                            </span>

                            <div class="flex-grow-1 lh-sm">
                                <b>
                                    {{ $tenantName !== '' ? $tenantName : 'Tenant' }}
                                    <span class="role-tag role-tag--user">User</span>
                                </b>
                            </div>
                            <button type="button" class="btn btn-link btn-square text-muted" aria-label="Options">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                            </button>
                        </div>

                        @isset($chatProperty)
                            <div class="chat-context">
                                <span class="flex-grow-1 text-muted">
                                    About <b class="text-body">{{ $chatProperty->title }}</b>

                                    @if ($chatProperty->price !== null)
                                        · {{ number_format((float) $chatProperty->price, 2) }} JOD
                                    @endif
                                </span>
                            </div>
                        @endisset

                        <div class="chat-body" id="chatBody" aria-live="polite">
                            @foreach ($messages as $message)
                                @php($isMine = (int) $message->sender_id === $currentUserId)

                                <div @class(['chat-message-row', 'is-mine' => $isMine]) data-message-id="{{ $message->id }}">
                                    <div @class(['msg', 'me' => $isMine])>
                                        @if ($message->replyTo)
                                            <blockquote class="small border-start border-2 ps-2 mb-1">
                                                {{ $message->replyTo->body }}
                                            </blockquote>
                                        @endif

                                        <span data-message-text>{{ $message->body }}</span>
                                        <div data-message-reactions class="d-flex flex-wrap gap-1">
                                            @foreach ($message->reactions as $reaction)
                                                <span
                                                    data-reaction-user="{{ $reaction->user_id }}">{{ $reaction->emoji }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <button type="button" class="chat-reply-button" data-reply-button
                                        aria-label="Reply to message" title="Reply">
                                        <i class="bi bi-reply" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="chat-reply-button" data-reaction-button
                                        aria-label="React to message" title="React">
                                        <i class="bi bi-emoji-smile" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div data-reply-preview class="px-3 py-2 border-top small" hidden>
                            <span>Replying to: </span>
                            <span data-reply-text></span>
                            <button type="button" class="btn btn-sm btn-link" data-cancel-reply>
                                Cancel
                            </button>
                        </div>

                        <form class="chat-input" id="chatForm" method="post"
                            action="{{ route('conversations.messages.store', ['conversation' => $conversation]) }}"
                            data-chat-form data-conversation-id="{{ $conversation->id }}"
                            data-current-user-id="{{ $currentUserId }}"
                            data-read-url="{{ route('conversations.read', ['conversation' => $conversation]) }}"
                            data-reaction-url="{{ route('conversations.messages.reaction', [
                                'conversation' => $conversation,
                                'messageId' => '__MESSAGE_ID__',
                            ]) }}">

                            @csrf
                            <input type="hidden" name="reply_to_id" value="">

                            <label for="chatMessage" class="visually-hidden">Your message</label>
                            <input type="text" class="form-control" id="chatMessage" name="body"
                                placeholder="Type your message..." autocomplete="off" required />

                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-send"></i>
                                <span>Send</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="chat-thread p-4 text-muted">
                        Select a conversation to view its messages.
                    </div>
                @endisset
            </div>
        </main>
    </div>
@endsection
