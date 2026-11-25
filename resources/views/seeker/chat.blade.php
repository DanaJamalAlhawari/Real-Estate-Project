@extends('layouts.app')

@section('title', __('Makan — Messages'))
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="chat"@endsection

@section('content')
    <!-- ===== Navbar ===== -->
    <x-seeker-navbar />

    <!-- ===== Content ===== -->
    <main class="app-page container">
        <!-- 1. Page header -->
        <div class="page-head mb-3">
            <h1>{{ __('Messages') }}</h1>
        </div>

        <div class="chat">
            <div class="chat-list" aria-label="{{ __('Conversations') }}">
                <div class="chat-list-head"><h2 class="h6 mb-0">{{ __('Conversations') }}</h2></div>

                <div id="chatList">
                    @forelse ($conversations as $chat)
                        @php($isActive = isset($conversation) && $conversation->is($chat))

                        <a href="{{ route('conversations.show', ['conversation' => $chat]) }}" @class(['chat-item', 'active' => $isActive])
                            @if ($isActive) aria-current="page" @endif>
                            <span class="avatar flex-shrink-0">
                                <i class="bi bi-chat-dots" aria-hidden="true"></i>
                            </span>

                            <span class="meta">
                                <b>{{ $chat->agent?->display_name ?? __('Conversation #:id', ['id' => $chat->id]) }}</b>

                                @if ($chat->last_message_at)
                                    <time>{{ \Illuminate\Support\Carbon::parse($chat->last_message_at)->translatedFormat('d M, g:i A') }}</time>
                                @endif
                            </span>

                            <span class="unread" data-unread-for="{{ $chat->id }}"
                                aria-label="{{ __(':count unread messages', ['count' => $chat->unread_count]) }}"
                                @if ($chat->unread_count == 0) hidden @endif>
                                {{ $chat->unread_count }}
                            </span>
                        </a>
                    @empty
                        <p class="text-muted px-3 py-2 mb-0">{{ __('No conversations yet.') }}</p>
                    @endforelse
                </div>
        <x-pagination :items="$conversations" />
            </div>

            @isset($conversation)
                <section class="chat-thread" aria-label="Conversation {{ $conversation->id }}">
                    <div class="chat-head">
                        <span class="avatar">A</span>
                        <div class="flex-grow-1 lh-sm">
                            <b>
                                {{ $ownerName !== '' ? $ownerName : __('Property owner') }}
                                <span class="role-tag role-tag--owner">{{ __(\Illuminate\Support\Str::headline($partnerRole ?? 'agent')) }}</span>
                            </b>

                        </div>
                        <div class="dropdown">
                            <button type="button" class="btn btn-link text-muted px-2" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="{{ __('Options') }}">
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
                                        <i class="bi bi-flag me-2"></i>{{ __('Report conversation') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @isset($chatProperty)
                        <div class="chat-context">
                            <span class="flex-grow-1 text-muted">
                                {{ __('About') }} <b class="text-body">{{ $chatProperty->localized('title') }}</b>

                                @if ($chatProperty->price !== null)
                                    · {{ number_format((float) $chatProperty->price, 2) }} {{ __('JOD') }}
                                @endif
                            </span>

                            <a href="{{ route('seeker.property.details', ['home' => $chatProperty->id]) }}" class="fw-semibold">
                                {{ __('View property') }}
                            </a>
                        </div>
                    @endisset
                    <x-pagination :items="$messages" />

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
                                    aria-label="{{ __('Reply to message') }}" title="{{ __('Reply') }}">
                                    <i class="bi bi-reply" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="chat-reply-button" data-reaction-button
                                    aria-label="{{ __('React to message') }}" title="{{ __('React') }}">
                                    <i class="bi bi-emoji-smile" aria-hidden="true"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div data-reply-preview class="px-3 py-2 border-top small" hidden>
                        <span>{{ __('Replying to:') }} </span>
                        <span data-reply-text></span>

                        <button type="button" class="btn btn-sm btn-link" data-cancel-reply>
                            {{ __('Cancel') }}
                        </button>
                    </div>

                    <form class="chat-input" id="chatForm" method="post"
                        action="{{ route('conversations.messages.store', ['conversation' => $conversation]) }}" data-chat-form
                        data-conversation-id="{{ $conversation->id }}"
                            @unless ($messages->onFirstPage())
                                data-latest-page="{{ $messages->url(1) }}"
                            @endunless data-current-user-id="{{ $currentUserId }}"
                        data-read-url="{{ route('conversations.read', ['conversation' => $conversation]) }}"
                        data-reaction-url="{{ route('conversations.messages.reaction', [
                            'conversation' => $conversation,
                            'messageId' => '__MESSAGE_ID__',
                        ]) }}">
                        @csrf
                        <input type="hidden" name="reply_to_id" value="">

                        <input type="text" class="form-control" name="body" aria-label="{{ __('Your message') }}"
                            placeholder="{{ __('Type your message...') }}" autocomplete="off" required />

                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-send"></i>{{ __('Send') }}
                        </button>
                    </form>
                </section>
            @else
                <div class="chat-thread p-4 text-muted">
                    {{ __('Select a conversation to view its messages.') }}
                </div>
            @endisset
        </div>
    </main>
@endsection
