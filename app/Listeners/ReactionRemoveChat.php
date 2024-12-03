<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Events\ReactionRemoved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReactionRemoveChat
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
    public function handle(ReactionRemoved $event): void
    {
        Notification::where('sender_id', Auth::id())
        ->where('receiver_id', $event->receiverId)
        ->where('message', 'You have a new reaction from user ' . $event->receiverId)
        ->delete();
    }
}
