<?php

namespace App\Models;

use App\Models\User;
use App\Models\MessagePublicGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class public_group extends Model
{
    use HasFactory;
    protected $table = 'public_groups';

    protected $fillable = [
        'name',
        'admin_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'public_group_users', 'public_group_id', 'user_id');
    }

    

    public function messages()
    {
        return $this->hasMany(MessagePublicGroup::class, 'group_id'); 
    }

    
}
