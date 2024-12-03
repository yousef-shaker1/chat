<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // use HasRoles;
    protected $fillable = ['name', 'guard_name'];
}
