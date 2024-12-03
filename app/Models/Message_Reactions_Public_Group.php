<?php

namespace App\Models;

use App\Models\User;
use App\Models\MessagePublicGroup;
use Illuminate\Database\Eloquent\Model;

class Message_Reactions_Public_Group extends Model
{
    protected $table = 'message_reactions_public_groups';
    protected $fillable = ['message_id', 'user_id', 'emoji'];

    public function message()
    {
        return $this->belongsTo(MessagePublicGroup::class, 'message_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
