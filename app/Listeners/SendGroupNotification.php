<?php

namespace App\Listeners;

use App\Events\GroupMessageSent;
use App\Models\NotificationGroup;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendGroupNotification
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
    public function handle(GroupMessageSent $event)
    {
        $groupMembers = $event->group->users()->pluck('users.id')->toArray();
        $groupAdmins = $event->group->admins()->pluck('users.id')->toArray();
        $allMembers = array_unique(array_merge($groupMembers, $groupAdmins));

        if (count($allMembers) > 0) {
            foreach ($allMembers as $userId) {
                if ($userId != $event->senderId) { 
                    NotificationGroup::create([
                        'sender_id' => $event->senderId,
                        'group_id' => $event->group->id,
                        'receiver_id' => $userId,
                        'message' => $event->message,
                    ]);
                }
            }
        }
    }
}
