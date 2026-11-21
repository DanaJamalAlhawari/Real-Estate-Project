<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(private Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversations.' . $this->message->conversation_id),
        ];
    }

    public function broadcastWith(): array
    {
        return $this->message->chatPayload();
    }
}
