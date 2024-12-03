<?php

namespace App\Listeners;

use App\Events\NotificationReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationToUser
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
    public function handle(NotificationReceived $event)
    {
        // إرسال الإشعار عبر Broadcasting
        broadcast(new NotificationReceived($event->notification));
    }
}
