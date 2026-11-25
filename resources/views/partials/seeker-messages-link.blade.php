<a class="icon-btn" href="{{ route('seeker.chat') }}"
    data-inbox-messages-link
    aria-label="{{ $unread > 0 ? __('Messages, :count unread', ['count' => $unread]) : __('Messages') }}">
    <i class="bi bi-chat-dots" aria-hidden="true"></i>

    <span class="icon-badge" data-inbox-unread
        @if ($unread === 0) hidden @endif>{{ $unread }}</span>
</a>