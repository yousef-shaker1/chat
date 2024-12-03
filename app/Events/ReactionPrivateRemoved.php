<?php

namespace App\Events;

use App\Models\MessageGroup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReactionPrivateRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $messageId;
    public $userId;
    public $groupId;

    /**
     * إنشاء حدث جديد.
     *
     * @return void
     */
    public function __construct($messageId, $userId)
    {
        $this->messageId = $messageId;
        $this->userId = $userId;

        // الحصول على بيانات الرسالة
        $message = MessageGroup::find($messageId);
        
        if ($message) {
            $this->groupId = $message->group_id;
        } else {
            throw new \Exception('Message not found');
        }
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // بث الحدث على القناة الخاصة بالجروب
        return new PrivateChannel('group.' . $this->groupId);
    }

    /**
     * اسم الحدث الذي يتم بثه.
     */
    public function broadcastAs()
    {
        return 'ReactionRemoved';
    }
}
