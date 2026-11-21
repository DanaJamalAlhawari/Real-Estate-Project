@extends('layouts.app')

@section('title', 'Makan — Messages')
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="chat"@endsection

@section('content')
    <!-- ===== Navbar ===== -->
    <x-seeker-navbar />

    <!-- ===== Content ===== -->
    <main class="app-page container">
        <!-- 1. Page header -->
        <div class="page-head mb-3">
            <h1>Messages</h1>
        </div>

        <div class="chat">
            <div class="chat-list" aria-label="Conversations">
                <div class="chat-list-head"><h2 class="h6 mb-0">Conversations</h2></div>

                <div id="chatList">
                    @forelse ($conversations as $chat)
                        @php($isActive = isset($conversation) && $conversation->is($chat))

                        <a href="{{ route('conversations.show', ['conversation' => $chat]) }}" @class(['chat-item', 'active' => $isActive])
                            @if ($isActive) aria-current="page" @endif>
                            <span class="avatar flex-shrink-0">
                                <i class="bi bi-chat-dots" aria-hidden="true"></i>
                            </span>

                            <span class="meta">
                                <b>{{ $chat->agent?->display_name ?? 'Conversation #'.$chat->id }}</b>

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
                                <span class="role-tag role-tag--owner">{{ ucfirst($partnerRole ?? 'agent') }}</span>
                            </b>

                        </div>
                        <div class="dropdown">
                            <button type="button" class="btn btn-link text-muted px-2" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Options">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
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
