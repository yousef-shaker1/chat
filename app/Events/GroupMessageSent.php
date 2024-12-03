<?php

namespace App\Events;

use App\Models\Group;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GroupMessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
      public $group;
    public $senderId;
    public $message;
    public $notificationMessage;

    /**
     * إنشاء حدث جديد.
     *
     * @return void
     */
    public function __construct(Group $group, $senderId, $notificationMessage)
    {
        $this->group = $group;
        $this->senderId = $senderId;
        $this->message = $notificationMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
     public function broadcastOn()
    {
        return new PrivateChannel('group.' . $this->group->id);
    }

    /**
     * اسم الحدث الذي يتم بثه.
     */
    public function broadcastAs()
    {
        return 'GroupMessageSent';
    }
}
