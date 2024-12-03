<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Events\ReactionAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReactionAddedChat
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
    public function handle(ReactionAdded $event): void
    {
        Notification::create([
            'sender_id' => $event->sender_id,
            'receiver_id' => $event->receiver_id,
            'message' => 'You have a new reaction from user ' . $event->receiver_id,
        ]);
    }
}
