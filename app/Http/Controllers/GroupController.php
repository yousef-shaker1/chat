<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Group_User;
use App\Models\GroupMember;
use Illuminate\Support\Str;
use App\Models\MessageGroup;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Events\ReactionRemoved;
use App\Events\GroupMessageSent;
use App\Models\NotificationGroup;
use App\Events\ReactionPrivateAdded;
use Illuminate\Support\Facades\Auth;
use App\Traits\ChecksGroupMembership;
use App\Events\ReactionPrivateRemoved;
use App\Models\Message_Reactions_Group;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class GroupController extends Controller
{
    use HttpResponses, ChecksGroupMembership;

    //create new group
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'can_send_messages' => 'required|boolean',
            'admin_ids' => 'nullable|array',
            'admin_ids.*' => 'exists:users,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'admin_id' => Auth::id(),
            'can_send_messages' => $request->can_send_messages ? 1 : 0,
            'organization_id' => $request->organization_id ?? null,
        ]);

        $group->admins()->attach(Auth::id());
        $group->admins()->attach($request->admin_ids);

        foreach ($request->user_ids as $userId) {
            $group->users()->attach($userId);
        }

        return $this->successResponse($group);
    }

    public function members(Request $request, $id){
        $group = Group::findOrFail($id);
        $Members = GroupMember::where('group_id', $id)->get();
        $usersWithImage = $Members->map(function($user) {
            $user->img = 'https://ipa.waslahq.com/images/default_image.jpeg';
            return $user;
        });
        return response()->json([
            'message' => 'Success',
            'data' => $usersWithImage
        ],200);
    }

    public function getusers(){
        $users = User::all();
        $usersWithImage = $users->map(function($user) {
            $user->img = 'https://ipa.waslahq.com/images/default_image.jpeg';
            return $user;
        });
        return response()->json([
            'message' => 'Success',
            'data' => $users
        ],200);
    }

    public function conversations()
    {
        $userId = Auth::id(); 

        $groupIdsFromMembers = GroupMember::where('user_id', $userId)->pluck('group_id');
        $groupIdsFromUsers = Group_User::where('user_id', $userId)->pluck('group_id');
        $allGroupIds = $groupIdsFromMembers->merge($groupIdsFromUsers)->unique();

        $groups = Group::with(['messages' => function($query) {
            $query->orderBy('created_at', 'desc')->take(1); 
        }])
        ->whereIn('id', $allGroupIds)
        ->get()
        ->map(function ($group) use ($userId) {
            $lastMessage = $group->messages->first(); 
            $unreadCount = NotificationGroup::where('receiver_id', $userId)
                ->where('group_id', $group->id)
                ->where('is_read', false) 
                ->count();

            return [
                'group_id' => $group->id,
                'name' => $group->name,
                'img' => 'https://ipa.waslahq.com/images/group_image.png',
                'last_message' => $lastMessage ? $lastMessage->message : null,
                'unread_notifications' => $unreadCount,
            ];
        });
        return response()->json([
            'message' => 'Success',
            'data' => $groups,
        ], 200);
    }

    public function searchgroup(Request $request, $id)
    {
        $userId = Auth::id();
        $searchQuery = $request->input('query');
    
        $messages = MessageGroup::where('group_id', $id) 
            ->where('message', 'LIKE', '%' . $searchQuery . '%')
            ->orderBy('created_at', 'desc')
            ->get();
        if (!$this->isMemberOrAdmin($id)) {
            return $this->errorResponse('You are not a member of this group', 403);
        }
        if (!$searchQuery) {
            return $this->errorResponse('Please provide a search query.', 400);
        }
        return $this->successResponse($messages);
    }

    //update group settings
    public function updateSettings(Request $request, $id)
    {
        $group = $this->getGroupWithAuthorization($id);

        if (!$group) {
            return $this->errorResponse('Unauthorized', 403);
        }
        
        $group->can_send_messages = $request->can_send_messages ? 1 : 0;
        $group->save();

        return $this->successResponse($group);
    }

    //add admin to group
    public function addAdmin(Request $request, $id)
    {
        $group = $this->getGroupWithAuthorization($id);

        if (!$group) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        foreach ($request->user_ids as $userId) {
            if ($group->users()->where('user_id', $userId)->exists()) {
                $group->users()->detach($userId);
            }
            $group->admins()->attach($userId);
        }
    
        return $this->successResponse('admin added successfully');
    }

    //remove admin from group
    public function removeAdmin(Request $request, $id){

        $group = $this->getGroupWithAuthorization($id);

        if (!$group) {
            return $this->errorResponse('Unauthorized', 403);
        }


        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        foreach ($request->user_ids as $userId) {
            $group->admins()->detach($userId);
        }
    
        return $this->successResponse('Admin(s) removed successfully');
    }

    //add users to group
    public function addUser(Request $request, $id)
    {
        $group = $this->getGroupWithAuthorization($id);

        if (!$group) {
            return $this->errorResponse('Unauthorized', 403);
        }
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $existingUserIds = $group->users()->pluck('group_user.user_id')->toArray();
        
        $adminUserIds = $group->admins()->pluck('group_members.user_id')->toArray();


        $newUserIds = array_diff($request->user_ids, $existingUserIds, $adminUserIds);
    
        if (empty($newUserIds)) {
            return response()->json(['message' => 'All users are already members of the group'], 400);
        }
        $group->users()->attach($newUserIds);

        return $this->successResponse('Members added successfully');
    }

    //remove users from group
    public function removeUser(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        if ($group->admin_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized',403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $existingUserIds = $group->users()->pluck('group_user.user_id')->toArray();

        $userIdsToRemove = array_intersect($request->user_ids, $existingUserIds);

        if (empty($userIdsToRemove)) {
            return response()->json(['message' => 'No users were removed because they are not members of the group'], 400);
        }

        $group->users()->detach($userIdsToRemove);

        return $this->successResponse('Members added successfully');
    }

    //get all  messages to group
    public function getMessages(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);
    
        if (!$this->isMemberOrAdmin($request->group_id)) {
            return $this->errorResponse('You are not a member of this group', 403);
        }

        $messages = MessageGroup::where('group_id', $request->group_id)
            ->orderBy('created_at', 'desc')
            ->limit(30) 
            ->with('reactions')
            ->get()
            ->reverse() 
            ->values();

            NotificationGroup::where('group_id', $request->group_id)
            ->where('receiver_id', Auth::id())
            ->delete();
    
        return $this->successResponse($messages);
    }

    //add Reaction to message
    public function addReaction(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:message_groups,id',
            'emoji' => 'required|string',
        ]);

        $message = MessageGroup::findOrFail($request->message_id);

        if (!$this->isMemberOrAdmin($message->group_id)) {
            return $this->errorResponse('You are not a member of this group', 403);
        }

        $reaction = Message_Reactions_Group::updateOrCreate(
            ['message_id' => $request->message_id, 'user_id' => Auth::id()],
            ['emoji' => $request->emoji]
        );
        event(new ReactionPrivateAdded($reaction));

        return $this->successResponse($reaction, 201);
    }

    //remove Reaction to message
    public function removeReaction(Request $request,$id)
    {
        $Reaction = Message_Reactions_Group::where('message_id', $id)->where('user_id', Auth::id())->first();

        if (!$Reaction) {
            return $this->errorResponse('Reaction not found', 404);
        }

        $Reaction->delete();
        event(new ReactionPrivateRemoved($id, Auth::id()));

        return $this->successResponse(null);
    }
    
    //send message to group
    public function sendMessage(Request $request)
    {    
        $group = Group::findOrFail($request->group_id);

        if (!$group) {
            return $this->errorResponse('Group not found', 404);
        }

        $canSendMessages = $group->can_send_messages;

        $isMember = $group->users()->where('user_id', Auth::id())->exists();
        $isAdmin = $group->admins()->where('user_id', Auth::id())->exists();
        $isAdminOfOrganization = Organization::where('admin_id', Auth::id())
        ->where('id', $group->organization_id) 
        ->exists();
        if ($canSendMessages) {
            if (!$isMember && !$isAdmin && !$isAdminOfOrganization) {
                return $this->errorResponse('You are not a member of this group',403);
            }
        } else {
            if (!$isAdmin && !$isAdminOfOrganization) {
                return $this->errorResponse('You cannot send messages in this group (admin only)',403);
            }
        }

        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'message' => 'required|string',
            'reply_to_message_id' => 'nullable|exists:message_groups,id',
        ]);

        $message = MessageGroup::create([
            'sender_id' => Auth::id(),
            'group_id' => $group->id,
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
        ]);
        event(new GroupMessageSent($group, Auth::id(), 'You have a new message in group ' . $group->name));
        return $this->successResponse($message, 201);
    }

    public function uploadFile(Request $request)
    {
        $group = Group::findOrFail($request->group_id);

        $canSendMessages = $group->can_send_messages;

        $isMember = $group->users()->where('user_id', Auth::id())->exists();
        $isAdmin = $group->admins()->where('user_id', Auth::id())->exists();
        $isAdminOfOrganization = Organization::where('admin_id', Auth::id())
        ->where('id', $group->organization_id) 
        ->exists();
        if ($canSendMessages) {
            if (!$isMember && !$isAdmin && !$isAdminOfOrganization) {
                return $this->errorResponse('You are not a member of this group',403);
            }
        } else {
            if (!$isAdmin && !$isAdminOfOrganization) {
                return $this->errorResponse('You cannot send messages in this group (admin only)',403);
            }
        }

        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'file' => 'required|file|max:2048',
            'reply_to_message_id' => 'nullable|exists:message_groups,id',
        ]);
    
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('group_file', $fileName, 'public');
                $data['file'] = 'group_file/' . $fileName; 
                $fileType = $file->getMimeType();
            }
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'file upload failed: ' . $e->getMessage(), 500);
        }

        $message = MessageGroup::create([
            'sender_id' => Auth::id(),
            'group_id' => $group->id,
            'file' => $data['file'],
            'type_file' => $fileType,
            'reply_to_message_id' => $data['reply_to_message_id'] ?? null,
        ]);

        event(new GroupMessageSent($group, Auth::id(), 'You have a new message in group ' . $group->name));
        
        return $this->successResponse($message, 201);
    }

    //get all  notifications
    public function getNotifications()
    {
        $userId = Auth::id();
    
        $notifications = NotificationGroup::where('receiver_id', $userId) 
            ->where('is_read', false)
            ->get();

        return response()->json([
            'message' => 'Success',
            'data' => $notifications,
            'unread_count' => $notifications->count() 
        ], 200);
    }

    //delete group
    public function deletegroup($id){
        $group = Group::findOrFail($id);

        if ($group->admin_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized',403);
        }
        $files = MessageGroup::where('group_id', $id)->pluck('file');

        foreach ($files as $file) {
            if (!empty($file) && Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }
        
        $group->delete();
        return $this->successResponse(null);
    }

    protected function getGroupWithAuthorization($id)
    {
        $group = Group::findOrFail($id); 
        $userId = Auth::id();

        $isAdminOfOrganization = Organization::where('admin_id', $userId)
            ->where('name', $group->organization->name)
            ->exists();

        if ($group->admin_id !== $userId && !$isAdminOfOrganization) {
            return null;
        }

        return $group; 
    }

}
