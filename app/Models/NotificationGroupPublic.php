<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationGroupPublic extends Model
{
    protected $table = 'notification_group_publics';
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'group_id',
        'message',
    ];
}
