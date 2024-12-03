<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//الوصول للشات الخاص
Broadcast::channel('chat.{senderId}.{receiverId}', function ($user, $senderId, $receiverId) {
    return (int) $user->id === (int) $senderId || (int) $user->id === (int) $receiverId;
});

//للشات الخاص
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return $user->id == $receiverId;
});

//للجروب العام
Broadcast::channel('public-group.{groupId}', function ($user, $groupId) {
    return true;
});

//للجروب الخاص
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return $user->groups()->where('group_id', $groupId)->exists();
});

//call
Broadcast::channel('call.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

