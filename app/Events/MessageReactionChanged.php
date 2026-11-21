<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MessageReactionChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        private int $conversationId,
        private int $messageId,
        private int $userId,
        private ?string $emoji,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversations.' . $this->conversationId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'emoji' => $this->emoji,
        ];
    }
}