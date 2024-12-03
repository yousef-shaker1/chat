<?php

namespace App\Listeners;

use App\Events\GroupPublicMessageSent;
use App\Models\NotificationGroupPublic;
use App\Models\public_group_user;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationPublic
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(GroupPublicMessageSent $event): void
    {
        $userIds = public_group_user::where('public_group_id', $event->group->id)->pluck('user_id');

        foreach ($userIds as $receiverId) {
            NotificationGroupPublic::create([
                'sender_id' => $event->senderId,
                'receiver_id' => $receiverId, 
                'group_id' => $event->group->id, 
                'message' => 'You have a new message from user ' . $event->senderId,
            ]);
        }
    }
}
