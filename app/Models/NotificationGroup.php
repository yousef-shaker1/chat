<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationGroup extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'group_id',
        'message',
        'is_read',
    ];
}
