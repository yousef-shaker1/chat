<?php

namespace App\Models;

use App\Models\User;
use App\Models\MessageGroup;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = [
        'name',
        'admin_id',
        'organization_id',
        'can_send_messages',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(MessageGroup::class, 'group_id'); 
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
}
