<?php

namespace App\Events;

use App\Models\MessageGroup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\Models\Message_Reactions_Group;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReactionPrivateAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $reaction;
    public $groupId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message_Reactions_Group $reaction)
    {
        $this->reaction = $reaction;

        $message = MessageGroup::find($reaction->message_id);

        if ($message) {
            $this->groupId = $message->group_id;
        } else {
            throw new \Exception('Message not found.');
        }
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('group.' . $this->groupId);
    }

    /**
     * اسم الحدث الذي سيتم بثه.
     */
    public function broadcastAs()
    {
        return 'ReactionAdded'; 
    }
}
