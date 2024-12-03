<?php

namespace App\Models;

use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = ['name', 'admin_id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'organization_id'); 
    }
}
