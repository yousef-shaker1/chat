<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group_User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\OrganizationUser;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    use HttpResponses;

    public function organization_all(){
        $userId = Auth::id();

        $groupIds = Group_User::where('user_id', $userId)->pluck('group_id')
                    ->merge(GroupMember::where('user_id', $userId)->pluck('group_id'));
    
        // $organizations = Organization::whereHas('groups', function($query) use ($groupIds) {
        //     $query->whereIn('id', $groupIds);
        // })->get();

        $organizations = Organization::where('admin_id', Auth::id())->with('groups')->get();
    
        return $this->successResponse($organizations);
    }
    public function create(Request $request){

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organization = Organization::create([
            'name' => $request->name,
            'admin_id' => Auth::id(),
        ]);
        OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
        ]);
        return $this->successResponse($organization);
    }

    public function delete($id){
        $organization = Organization::find($id);
        if(!$organization){
            return $this->errorResponse('Organization not found', 404);
        }
        if($organization->admin_id != Auth::id()){
            return $this->errorResponse('You are not authorized to delete this organization', 403);
        }
        $organization->delete();
        return $this->successResponse(null);
    }
}
