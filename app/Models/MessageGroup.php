<?php

namespace App\Models;

use App\Models\Group;
use App\Models\public_group;
use App\Models\Message_Reactions_Group;
use Illuminate\Database\Eloquent\Model;

class MessageGroup extends Model
{
    protected $fillable = [
        'sender_id',
        'group_id',
        'message',
        'file',
        'type_file',
        'reply_to_message_id',
    ];

    public function reactions()
    {
        return $this->hasMany(Message_Reactions_Group::class, 'message_id')->select('message_id','user_id', 'emoji');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id'); 
    }


}
