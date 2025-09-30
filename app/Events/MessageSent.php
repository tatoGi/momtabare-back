<?php

namespace App\Events;

use App\Models\ChatMessages;
use App\Models\WebUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public WebUser $user, public ChatMessages $chatMessage)
    {
        $this->user = $user;
        $this->chatMessage = $chatMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat'),
        ];
    }

    public function broadcastWith()
    {
        return ['message' => $this->chatMessage];
    }
}
