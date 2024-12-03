<?php

namespace App\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\MessageShow;
use App\Models\MessageChat;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Events\ReactionAdded;
use App\Traits\HttpResponses;
use App\Events\MessageDeleted;
use App\Events\ReactionRemoved;
use App\Events\ReactionUpdated;
use App\Models\Message_Reaction;
use App\Events\NotificationReceived;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    use HttpResponses;

    //get all conversations The ones I talked to
    public function getConversations()
    {
        $userId = Auth::id();

        $conversations = MessageChat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->select('sender_id', 'receiver_id')
            ->distinct()
            ->get();
    
        $uniqueConversations = [];
    
        foreach ($conversations as $conversation) {
            $key = $conversation->sender_id < $conversation->receiver_id 
                ? $conversation->sender_id . '-' . $conversation->receiver_id 
                : $conversation->receiver_id . '-' . $conversation->sender_id;
    
            if (!isset($uniqueConversations[$key])) {
                $lastMessage = MessageChat::where(function ($query) use ($conversation) {
                    $query->where('sender_id', $conversation->sender_id)
                          ->where('receiver_id', $conversation->receiver_id);
                })->orWhere(function ($query) use ($conversation) {
                    $query->where('sender_id', $conversation->receiver_id)
                          ->where('receiver_id', $conversation->sender_id);
                })
                ->orderBy('created_at', 'desc')
                ->first();
    
                $unreadCount = Notification::where('receiver_id', Auth::id())
                    ->where('sender_id', $conversation->sender_id)
                    ->where('is_read', false)
                    ->count();
    
                $lastMessageContent = null;
                if ($lastMessage) {
                    if (!empty($lastMessage->message)) {
                        $lastMessageContent = $lastMessage->message;
                    } elseif (!empty($lastMessage->file)) {
                        $lastMessageContent = 'file';
                    }
                }
                $senderName = User::find($conversation->sender_id)->name ?? 'Unknown';
                $uniqueConversations[$key] = [
                    'sender_id' => $conversation->sender_id,
                    'sender_name' => $senderName,
                    'img' => 'https://ipa.waslahq.com/images/default_image.jpeg',
                    'receiver_id' => $conversation->receiver_id,
                    'last_message' => $lastMessageContent,
                    'unread_count' => $unreadCount,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at : null, 
                ];
            }
        }
        usort($uniqueConversations, function ($a, $b) {
            return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
        });

        $result = array_values($uniqueConversations);

        event(new MessageShow($result));
        return $this->successResponse($result);
    }

    //fint chat
    public function searchChats(Request $request)
    {
        $userId = Auth::id();
        $search = $request->input('search');
    
        if (!$search) {
            return $this->errorResponse('Please provide a search query.', 400);
        }
    
        $users = User::where('name', 'LIKE', '%' . $search . '%')
            ->where('id', '!=', $userId)
            ->select('id', 'name')
            ->get();
    
        $results = [];
    
        foreach ($users as $user) {
            $lastMessage = MessageChat::where(function ($query) use ($userId, $user) {
                    $query->where('sender_id', $userId)->where('receiver_id', $user->id);
                })
                ->orWhere(function ($query) use ($userId, $user) {
                    $query->where('sender_id', $user->id)->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->first();
    
            $unreadCount = Notification::where('receiver_id', Auth::id())
                ->where('sender_id', $user->id)
                ->where('is_read', false)
                ->count();

            $results[] = [
                'id' => $user->id,
                'name' => $user->name,
                'img' => 'https://ipa.waslahq.com/images/default_image.jpeg',
                'last_message' => $lastMessage ? $lastMessage->message : null,
                'unread_count' => $unreadCount,
            ];
        }
    
        return $this->successResponse($results);
    }

    //fint massages in chat
    public function searchChat(Request $request, $id)
    {
        $userId = Auth::id();
        $searchQuery = $request->input('query');

        if (!$searchQuery) {
            return $this->errorResponse('Please provide a search query.', 400);
        }

        $messages = MessageChat::where(function ($query) use ($searchQuery, $id, $userId) {
            $query->where('message', 'LIKE', '%' . $searchQuery . '%')
                  ->where('receiver_id', $id)
                  ->where('sender_id', $userId);
        })
        ->orWhere(function ($query) use ($searchQuery, $id, $userId) {
            $query->where('message', 'LIKE', '%' . $searchQuery . '%')
                  ->where('receiver_id', $userId)
                  ->where('sender_id', $id);
        })
        ->orderBy('created_at', 'desc')
        ->get();
    
        return $this->successResponse($messages);
    }

    //send message chat
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'reply_to_message_id' => 'nullable|exists:message_chats,id',
        ]);

        $message = MessageChat::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
        ]);

        event(new MessageSent($message->sender_id, $message->receiver_id, $message->message));

        return $this->successResponse($message,201);
    }

    //upload file chat
    public function uploadFile(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'file' => 'required|file|max:2048',
            'reply_to_message_id' => 'nullable|exists:message_chats,id',
        ]);
    
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('file', $fileName, 'public');
                $data['file'] = 'file/' . $fileName; 
                $fileType = $file->getMimeType(); 
            }
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Image upload failed: ' . $e->getMessage(), 500);
        }

        $message = MessageChat::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'file' => $data['file'],
            'type_file' => $fileType,
            'reply_to_message_id' => $data['reply_to_message_id'] ?? null,
        ]);
        event(new MessageSent($message->sender_id, $message->receiver_id, $data['file']));
    
        return $this->successResponse($message, 201);
    }

    public function removeMessage(Request $request, $id)
    {
        $message = MessageChat::find($id);
    
        if (!$message) {
            return $this->errorResponse('Message not found', 404);
        }

        if ($message->file) {
            $filePath = 'group_public_file/' . basename($message->file);
    
            if (!empty($message->file) && Storage::disk('public')->exists($message->file)) {
                Storage::disk('public')->delete($message->file);
            } else {
                return $this->errorResponse('File not found in storage', 404);
            }
        }

        $userId = Auth::id();
        event(new MessageDeleted($id, $userId));
        
        Message_Reaction::where('message_id', $id)->delete();
        $message->update([
            'is_deleted' => 1,
        ]);
        
        return $this->successResponse(null);
    }

    //get all messages chat
    public function getMessages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'limit' => 'integer|min:1|max:100',
        ]);
        $limit = $request->input('limit', 30);
        Notification::where('sender_id', $request->user_id)
        ->where('receiver_id', Auth::id())
        ->delete();

        $messages = MessageChat::where(function ($query) use ($request) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $request->user_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->user_id)
                  ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'desc')
        ->with('reactions')
        ->limit($limit) 
        ->get()
        ->reverse() 
        ->values();
        
        return response()->json([
            'data' => $messages,  
        ], 200);
    }

    //get all  notifications
    public function getNotifications()
    {
        $userId = Auth::id();
    
        $notifications = Notification::where('receiver_id', $userId) 
            ->where('is_read', false) 
            ->get();
        return response()->json([
            'message' => 'Success',
            'data' => $notifications,
            'unread_count' => $notifications->count() 
        ], 200);
    }

    //add Reaction to message
    public function addReaction(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:message_chats,id',
            'emoji' => 'required|string',
        ]);

        $existingReaction = Message_Reaction::where('message_id', $request->message_id)
        ->where('user_id', Auth::id())
        ->first();

        if ($existingReaction) {
        return $this->errorResponse('You have already reacted to this message', 400);
        }
        $reaction = Message_Reaction::updateOrCreate(
            ['message_id' => $request->message_id, 'user_id' => Auth::id()],
            ['emoji' => $request->emoji]
        );

        $message = MessageChat::find($request->message_id);
        $receiver_id = ($message->sender_id === Auth::id()) ? $message->receiver_id : $message->sender_id;

        $UserId = Auth::id();
        event(new ReactionAdded($reaction,$UserId, $receiver_id));

        return $this->successResponse($reaction, 201);
        return response()->json([
            'message' => 'Success',
            'data' => $reaction,
            'user_id' => Auth::id(),
        ], $code);
    }

    public function editReaction(Request $request){
        $request->validate([
            'message_id' => 'required|exists:message_chats,id',
            'emoji' => 'required|string',
        ]);

        $Reaction = Message_Reaction::where('message_id', $request->message_id)->where('user_id', Auth::id())->first();
        if (!$Reaction) {
            return $this->errorResponse('Reaction not found', 404);
        }
        $Reaction->update([
            'emoji' => $request->emoji,
        ]);
        $sender_id = Auth::id();
    

        $message = MessageChat::find($request->message_id);
        $receiver_id = ($message->sender_id === Auth::id()) ? $message->receiver_id : $message->sender_id;

        event(new ReactionUpdated($Reaction,$sender_id, $receiver_id));
        return $this->successResponse($Reaction);

    }
    //remove Reaction to message
    public function removeReaction(Request $request,$id)
    {
        $Reaction = Message_Reaction::where('message_id', $id)->first();

        if (!$Reaction) {
            return $this->errorResponse('Reaction not found', 404);
        }
        event(new ReactionRemoved($id, Auth::id()));

        $Reaction->delete();

        return $this->successResponse(null);
    }

}

