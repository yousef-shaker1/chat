<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group_User extends Model
{
    protected $table = "group_user";
    protected $fillable = ['group_id', 'user_id'];
}
