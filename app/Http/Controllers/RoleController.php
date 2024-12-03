<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;


class RoleController extends Controller
{
    public function manageRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);

        if ($user && $role) {
            $user->assignRole($role->name);
            return response()->json(['message' => 'Role assigned successfully.']);
        }

        return response()->json(['message' => 'Failed to assign role.'], 400);
    }


}

