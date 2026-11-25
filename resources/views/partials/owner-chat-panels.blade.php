            <div class="chat">
                <!-- Conversation list -->
                <div class="chat-list" id="conversationList">
                    <div class="chat-list-head"><h2 class="h6 mb-0">{{ __('Conversations') }}</h2></div>

                    @forelse ($conversations as $chat)
                        @php($isActive = isset($conversation) && $conversation->is($chat))

                        <a href="{{ route('conversations.show', ['conversation' => $chat]) }}"
                            @class(['chat-item', 'active' => $isActive]) @if ($isActive) aria-current="page" @endif>
                            <span class="avatar avatar-lg">
                                <i class="bi bi-chat-dots" aria-hidden="true"></i>
                            </span>

                            <span class="meta">
                                <b>{{ ((int) $chat->tenant_id === (int) auth()->id() ? $chat->agent : $chat->tenant)?->display_name ?? __('Conversation #:id', ['id' => $chat->id]) }}</b>

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
                @isset($conversation)
                    <div class="chat-thread" id="chatThread">
                        <div class="chat-head">
                            <span class="avatar avatar-lg">
                                {{ $tenantName !== '' ? mb_strtoupper(mb_substr($tenantName, 0, 1)) : 'T' }}
                            </span>

                            <div class="flex-grow-1 lh-sm">
                                <b>
                                    {{ $tenantName !== '' ? $tenantName : __('Tenant') }}
                                    <span class="role-tag role-tag--user">{{ __(\Illuminate\Support\Str::headline($partnerRole ?? 'tenant')) }}</span>
                                </b>
                            </div>
                            <button type="button" class="btn btn-link btn-square text-muted" aria-label="{{ __('Options') }}">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                            </button>
                        </div>

                        @isset($chatProperty)
                            <div class="chat-context">
                                <span class="flex-grow-1 text-muted">
                                    {{ __('About') }} <b class="text-body">{{ $chatProperty->localized('title') }}</b>

                                    @if ($chatProperty->price !== null)
                                        · {{ number_format((float) $chatProperty->price, 2) }} {{ __('JOD') }}
                                    @endif
                                </span>
                            </div>
                        @endisset
                    <x-pagination :items="$messages" />

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
                            action="{{ route('conversations.messages.store', ['conversation' => $conversation]) }}"
                            data-chat-form data-conversation-id="{{ $conversation->id }}"
                            @unless ($messages->onFirstPage())
                                data-latest-page="{{ $messages->url(1) }}"
                            @endunless
                            data-current-user-id="{{ $currentUserId }}"
                            data-read-url="{{ route('conversations.read', ['conversation' => $conversation]) }}"
                            data-reaction-url="{{ route('conversations.messages.reaction', [
                                'conversation' => $conversation,
                                'messageId' => '__MESSAGE_ID__',
                            ]) }}">

                            @csrf
                            <input type="hidden" name="reply_to_id" value="">

                            <label for="chatMessage" class="visually-hidden">{{ __('Your message') }}</label>
                            <input type="text" class="form-control" id="chatMessage" name="body"
                                placeholder="{{ __('Type your message...') }}" autocomplete="off" required />

                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-send"></i>
                                <span>{{ __('Send') }}</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="chat-thread p-4 text-muted">
                        {{ __('Select a conversation to view its messages.') }}
                    </div>
                @endisset
            </div>
