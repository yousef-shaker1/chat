<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class public_group_user extends Model
{
    protected $fillable = [
        'user_id',
        'public_group_id'
    ];
}
