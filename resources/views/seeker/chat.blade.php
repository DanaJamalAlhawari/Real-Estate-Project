@extends('layouts.app')

@section('title', 'Makan — Messages')
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="chat"@endsection

@section('content')
    <!-- ===== Navbar ===== -->
    <header class="site-header app-nav">
        <nav class="navbar navbar-expand-lg" aria-label="Main">
            <div class="container">
                <a class="brand" href="{{ route('seeker.search') }}"><span class="brand-mark">M</span><span>Makan</span></a>

                <!-- Messages, notifications + user menu (stay visible on phones) -->
                <div class="d-flex align-items-center gap-1 ms-auto order-lg-last">
                    <!-- Messages: icon with unread count -->
                    @include('partials.seeker-messages-link')
                    <div class="dropdown">
                        <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Notifications">
                            <i class="bi bi-bell"></i><span class="dot"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 320px">
                            <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
                            <div class="py-1">
                                <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ route('seeker.property') }}">
                                    <span class="kpi-icon flex-shrink-0"
                                        style="width: 34px; height: 34px; font-size: 1rem"><i
                                            class="bi bi-stars"></i></span>
                                    <span class="small">
                                        <span class="d-block">A new home just listed in Al Jubeiha with a 92%
                                            match</span>
                                        <span class="text-muted" style="font-size: 0.72rem">10 minutes ago</span>
                                    </span>
                                </a>
                                <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ route('seeker.bookings') }}">
                                    <span class="kpi-icon flex-shrink-0"
                                        style="width: 34px; height: 34px; font-size: 1rem"><i
                                            class="bi bi-check2-circle"></i></span>
                                    <span class="small">
                                        <span class="d-block">Abu Ahmad accepted your booking</span>
                                        <span class="text-muted" style="font-size: 0.72rem">1 hour ago</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="app-user app-user--avatar" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false" aria-label="Account menu, Sara Khaled">
                            <span class="avatar">S</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <h6 class="dropdown-header">Sara Khaled</h6>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('seeker.profile') }}">My profile</a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li>
                                <x-logout-form />
                            </li>
                        </ul>
                    </div>
                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                        data-bs-target="#appNav" aria-controls="appNav" aria-expanded="false" aria-label="Menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <!-- Page links + search, grouped in one capsule -->
                <div class="collapse navbar-collapse" id="appNav">
                    <div class="nav-group">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('seeker.search') }}"><i
                                        class="bi bi-stars"></i><span>Discover</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('seeker.bookings') }}"><i
                                        class="bi bi-calendar-check"></i><span>My bookings</span><span
                                        class="side-badge">1</span></a>
                            </li>
                        </ul>
                        <span class="nav-group-divider" aria-hidden="true"></span>
                        <!-- Search: plain GET form, opens the Discover page -->
                        <form class="nav-search" action="{{ route('seeker.search') }}" method="get" role="search">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search" class="form-control" name="q" placeholder="Search by area..."
                                aria-label="Search homes by neighborhood" />
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- ===== Content ===== -->
    <main class="app-page container">
        <!-- 1. Page header -->
        <div class="page-head mb-3">
            <h1>Messages</h1>
        </div>

        <div class="chat">
            <div class="chat-list" aria-label="Conversations">
                <form class="chat-list-head" role="search" id="chatSearch" method="post"
                    action="{{ route('demo.notice') }}" data-demo-form>
                    @csrf
                    <div class="search-input">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" class="form-control" name="q" placeholder="Search conversations..."
                            aria-label="Search conversations" />
                    </div>
                </form>

                <div id="chatList">
                    @forelse ($conversations as $chat)
                        @php($isActive = isset($conversation) && $conversation->is($chat))

                        <a href="{{ route('conversations.show', ['conversation' => $chat]) }}" @class(['chat-item', 'active' => $isActive])
                            @if ($isActive) aria-current="page" @endif>
                            <span class="avatar flex-shrink-0">
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
            </div>

            @isset($conversation)
                <section class="chat-thread" aria-label="Conversation {{ $conversation->id }}">
                    <div class="chat-head">
                        <span class="avatar">A</span>
                        <div class="flex-grow-1 lh-sm">
                            <b>
                                {{ $ownerName !== '' ? $ownerName : 'Property owner' }}
                                <span class="role-tag role-tag--owner">Owner</span>
                            </b>

                        </div>
                        <div class="dropdown">
                            <button type="button" class="btn btn-link text-muted px-2" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Options">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('seeker.property') }}">
                                        <i class="bi bi-house-door me-2"></i>View property
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider" />
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger">
                                        <i class="bi bi-flag me-2"></i>Report conversation
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @isset($chatProperty)
                        <div class="chat-context">
                            <span class="flex-grow-1 text-muted">
                                About <b class="text-body">{{ $chatProperty->title }}</b>

                                @if ($chatProperty->price !== null)
                                    · {{ number_format((float) $chatProperty->price, 2) }} JOD
                                @endif
                            </span>

                            <a href="{{ route('seeker.property.details', ['home' => $chatProperty->id]) }}" class="fw-semibold">
                                View property
                            </a>
                        </div>
                    @endisset

                    <div class="chat-body" id="chatMessages" aria-live="polite">
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
                                            <span data-reaction-user="{{ $reaction->user_id }}">{{ $reaction->emoji }}</span>
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
                        action="{{ route('conversations.messages.store', ['conversation' => $conversation]) }}" data-chat-form
                        data-conversation-id="{{ $conversation->id }}" data-current-user-id="{{ $currentUserId }}"
                        data-read-url="{{ route('conversations.read', ['conversation' => $conversation]) }}"
                        data-reaction-url="{{ route('conversations.messages.reaction', [
                            'conversation' => $conversation,
                            'messageId' => '__MESSAGE_ID__',
                        ]) }}">
                        @csrf
                        <input type="hidden" name="reply_to_id" value="">

                        <input type="text" class="form-control" name="body" aria-label="Your message"
                            placeholder="Type your message..." autocomplete="off" required />

                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-send"></i>Send
                        </button>
                    </form>
                </section>
            @else
                <div class="chat-thread p-4 text-muted">
                    Select a conversation to view its messages.
                </div>
            @endisset
        </div>
    </main>
@endsection
