<?php

namespace App\Events;

use App\Models\Message_Reactions_Public_Group;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReactionPublicAdded
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
    public function __construct(Message_Reactions_Public_Group $reaction, $groupId)
    {
        $this->reaction = $reaction;
        $this->groupId = $groupId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('public-group.' . $this->groupId);
    }
}
