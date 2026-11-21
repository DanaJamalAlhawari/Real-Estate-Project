<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Str;

class InboxMessageReceived implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        private Message $message,
        private int $recipientId,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inbox.' . $this->recipientId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'preview' => Str::limit($this->message->body, 80),
            'sent_at' => $this->message->created_at->toIso8601String(),
            'url' => route('conversations.show', [
                'conversation' => $this->message->conversation_id,
            ], false),
        ];
    }
}