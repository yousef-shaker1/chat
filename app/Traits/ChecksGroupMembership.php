<?php

namespace App\Traits;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;

trait ChecksGroupMembership
{
    public function isMemberOrAdmin($groupId)
    {
        $group = Group::findOrFail($groupId);
        
        $isMember = $group->users()->where('user_id', Auth::id())->exists();
        $isAdmin = $group->admins()->where('user_id', Auth::id())->exists();

        return $isMember || $isAdmin;
    }
}
