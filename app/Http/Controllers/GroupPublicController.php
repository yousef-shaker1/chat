<?php

namespace App\Http\Controllers;

use App\Models\Message_Reactions_Public_Group;
use Illuminate\Support\Str;
use App\Models\public_group;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\NotificationGroup;
use App\Models\public_group_user;
use App\Models\MessagePublicGroup;
use App\Events\ReactionPublicAdded;
use App\Events\MessagePublicDeleted;
use Illuminate\Support\Facades\Auth;
use App\Events\ReactionPublicRemoved;
use App\Events\GroupPublicMessageSent;
use App\Models\NotificationGroupPublic;
use Illuminate\Support\Facades\Storage;

class GroupPublicController extends Controller
{
    use HttpResponses;   

    //all public groups
    public function index(){
        $groups = public_group::all();
        $user_groups = public_group_user::where('user_id', Auth::id())->pluck('public_group_id')->toArray();

        $data = $groups->map(function ($group) use ($user_groups) {
            $group->in_group = in_array($group->id, $user_groups);
            
            $group->image = "https://ipa.waslahq.com/images/group_image.png";
            
            $lastMessage = MessagePublicGroup::where('group_id', $group->id)
                ->latest() 
                ->first(); 
            
            $group->last_message = $lastMessage ? $lastMessage->message : null; 
            
            return $group;
        });

        return response()->json([
            'message' => "success",
            'data' => $data,
        ], 200);
    }

    public function conversations()
    {
        $userId = Auth::id();

        $groupIds = public_group_user::where('user_id', $userId)->pluck('public_group_id');

        $groups = public_group::with(['messages' => function($query) {
            $query->orderBy('created_at', 'desc')->take(1);
        }])
        ->whereIn('id', $groupIds)
        ->get()
        ->map(function ($group) use ($userId) {
            $lastMessage = $group->messages->first();
            $unreadCount = NotificationGroupPublic::where('receiver_id', $userId)
                ->where('group_id', $group->id)
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
    //create public group
    public function create(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = public_group::create([
            'name' => $request->name,
            'admin_id' => Auth::id(),
        ]);

        public_group_user::create([
            'public_group_id' => $group->id,
            'user_id' => $group->admin_id,
        ]);

        return $this->successResponse($group);
    }

    //join public group
    public function join($id)
    {
        $group = public_group::findOrFail($id);
        if(!$group){
            return $this->errorResponse('Group not found', 404);
        }

        public_group_user::create([
            'public_group_id' => $group->id,
            'user_id' => Auth::id(),
        ]);
        return $this->successResponse('join to group successfully');
    }

    //send message public group
    public function sendmessage(Request $request){

        $request->validate([
            'group_id' => 'required|exists:public_groups,id',
            'message' => 'required|string|max:255',
            'reply_to_message_id' => 'nullable|exists:message_public_groups,id',
        ]);

        $group = public_group::find($request->group_id);
        if(!$group){
            return $this->errorResponse('Group not found', 404);
        }

        if ($group->admin_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $message = MessagePublicGroup::create([
            'sender_id' => Auth::id(),
            'group_id' => $group->id,
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
        ]);
        
        $users = public_group_user::where('public_group_id', $group->id)->pluck('user_id');
        
        foreach ($users as $userId) {
            event(new GroupPublicMessageSent($group, $userId, 'You have a new message in group ' . $group->name));
        }
        return $this->successResponse($message);
    }

    public function uploadFile(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:public_groups,id',
            'file' => 'required|file|max:2048',
            'reply_to_message_id' => 'nullable|exists:message_public_groups,id',
        ]);

        $group = public_group::find($request->group_id);
        if(!$group){
            return $this->errorResponse('Group not found', 404);
        }

        if ($group->admin_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('group_public_file', $fileName, 'public');
                $data['file'] = 'group_public_file/' . $fileName; 
                $fileType = $file->getMimeType(); 
            }
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'file upload failed: ' . $e->getMessage(), 500);
        }

        $message = MessagePublicGroup::create([
            'sender_id' => Auth::id(),
            'group_id' => $request->group_id,
            'file' => $data['file'],
            'type_file' => $fileType,
            'reply_to_message_id' => $request->reply_to_message_id,
        ]);
        $users = public_group_user::where('public_group_id', $group->id)->pluck('user_id');
    
        foreach ($users as $userId) {
            event(new GroupPublicMessageSent($group, $userId, 'You have a new message in group ' . $group->name));
        }
        return $this->successResponse($message);
    }
    //get messages public group
    public function getMessages(Request $request,$id){
        $request->validate([
            'limit' => 'integer|min:1|max:100',
        ]);
        $limit = $request->input('limit', 30);
        $group = public_group::find($id);
        
        if(!$group){
            return $this->errorResponse('Group not found', 404);
        }

        $messages = MessagePublicGroup::where('group_id', $id)
        ->orderBy('created_at', 'desc')
        ->limit(30)
        ->with('reactions')
        ->get()
        ->reverse()
        ->values();

        NotificationGroupPublic::where('group_id', $id)
        ->where('receiver_id', Auth::id())
        ->delete();
        
        return $this->successResponse($messages);
    }
        
    //add Reaction to message
    public function addReaction(Request $request,$id)
    {
        $request->validate([
            'message_id' => 'required|exists:message_public_groups,id',
            'emoji' => 'required|string',
        ]);

        $message = MessagePublicGroup::find($request->message_id);

        if (!$message) {
            return $this->errorResponse('Message not found', 404);
        }
        $group = public_group::find($id);
        $userId = Auth::id();
        $users = public_group_user::where('public_group_id', $id)->pluck('user_id');
        if (!$users->contains($userId)) {//contains للتحقق من وجود قيمة معينة في مجموعة ولا لا
            return $this->errorResponse('You are not a member of this group', 403);
        }

        $reaction = Message_Reactions_Public_Group::updateOrCreate(
            ['message_id' => $message->id, 'user_id' => Auth::id()],
            ['emoji' => $request->emoji]
        );

        event(new ReactionPublicAdded($reaction, $group->id));
        return $this->successResponse($reaction, 201);
    }

    //remove Reaction to message
    public function removeReaction(Request $request,$id)
    {
        $Reaction = Message_Reactions_Public_Group::where('message_id', $id)->where('user_id', Auth::id())->first();
        if (!$Reaction) {
            return $this->errorResponse('Reaction not found', 404);
        }
        $groupId = $Reaction->message ? $Reaction->message->group_id : null;
        if (!$groupId) {
            return $this->errorResponse('Group not found', 404);
        }

        $Reaction->delete();
        event(new ReactionPublicRemoved($Reaction, $groupId));

        return $this->successResponse(null);
    }

    public function deleteMessage($id)
    {
        $message = MessagePublicGroup::find($id);

        if (!$message) {
            return $this->errorResponse('Message not found', 404);
        }
        $group = public_group::find($message->group_id); 
        
        if (!$group) {
            return $this->errorResponse('Group not found', 404);
        }

        if ($message->file) {
            $filePath = 'group_public_file/' . basename($message->file);
    
            if (!empty($message->file) && Storage::disk('public')->exists($message->file)) {
                Storage::disk('public')->delete($message->file);
            } else {
                return $this->errorResponse('File not found in storage', 404);
            }
        }
        
        $message->delete();
        event(new MessagePublicDeleted());
        
        return $this->successResponse('Message deleted successfully');
    }
}
