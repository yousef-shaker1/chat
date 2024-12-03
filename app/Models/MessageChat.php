<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Message_Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageChat extends Model
{
    use HasFactory;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'file',
        'type_file',
        'is_deleted',
        'reply_to_message_id',
    ];

    public function reactions()
    {
        return $this->hasMany(Message_Reaction::class, 'message_id')->select('message_id', 'emoji');
    }


}
