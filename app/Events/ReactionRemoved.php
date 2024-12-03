<?php

namespace App\Events;

use App\Models\MessageChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReactionRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $messageId;
    public $userId;
    public $senderId;
    public $receiverId;

    public function __construct($messageId, $userId)
    {
        $this->messageId = $messageId;
        $this->userId = $userId;

        // Get the sender and receiver based on the message ID
        $message = MessageChat::find($messageId);
        if ($message) {
            $this->senderId = $message->sender_id;
            $this->receiverId = $message->receiver_id;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . min($this->senderId, $this->receiverId) . '.' . max($this->senderId, $this->receiverId));
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
        ];
    }
}
