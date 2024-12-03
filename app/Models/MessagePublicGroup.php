<?php

namespace App\Models;

use App\Models\public_group;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message_Reactions_Public_Group;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessagePublicGroup extends Model
{
    use HasFactory;
    protected $fillable = ['sender_id', 'group_id', 'message', 'file', 'type_file','reply_to_message_id'];

    public function reactions()
    {
        return $this->hasMany(Message_Reactions_Public_Group::class, 'message_id')->select('message_id','user_id', 'emoji');
    }

    public function group()
    {
        return $this->belongsTo(public_group::class, 'group_id'); 
    }
}
