<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class BookingActivity implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        private int $recipientId,
        private string $message,
        private string $url,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('inbox.'.$this->recipientId);
    }

    public function broadcastWith(): array
    {
        return ['preview' => $this->message, 'url' => $this->url];
    }
}
