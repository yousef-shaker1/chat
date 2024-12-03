<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotification
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
    public function handle(MessageSent $event): void
    {
        Notification::create([
            'sender_id' => $event->senderId,
            'receiver_id' => $event->receiverId,
            'message' => 'You have a new message from user ' . $event->senderId,
        ]);
    }
}
