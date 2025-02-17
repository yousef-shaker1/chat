<?php

use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupPublicController;
use App\Http\Controllers\OrganizationController;
//https://ipa.waslahq.com

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// 'role:superadmin'
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/manage-roles', [RoleController::class, 'manageRoles']);
    
    //private chat routes
    Route::get('/chats/search', [ChatController::class, 'searchChats']);
    Route::get('/chats/conversations', [ChatController::class, 'getConversations']);
    Route::get('/messages', [ChatController::class, 'getMessages']);
    Route::get('/chat/search/{id}', [ChatController::class, 'searchChat']);
    Route::post('/message/send', [ChatController::class, 'sendMessage']);
    Route::post('/message/file', [ChatController::class, 'uploadFile']);
    Route::delete('/message/remove/{id}', [ChatController::class, 'removeMessage']);
    Route::post('/message/reaction/add', [ChatController::class, 'addReaction']);
    Route::post('/message/reaction/edit', [ChatController::class, 'editReaction']);
    Route::delete('/message/remove/Reaction/{id}', [ChatController::class, 'removeReaction']);
    Route::get('/Notifications', [ChatController::class, 'getNotifications']);

    //organization routes 
    Route::get('/organization/all', [OrganizationController::class, 'organization_all']);
    Route::post('/organization/create', [OrganizationController::class, 'create']);
    Route::delete('/organization/delete/{id}', [OrganizationController::class, 'delete']);

    //group private chat routes
    Route::post('/create/group', [GroupController::class, 'create']);
    Route::get('/user/all', [GroupController::class, 'getusers']);
    Route::get('/group/conversations', [GroupController::class, 'conversations']);
    Route::get('/group/members/{id}', [GroupController::class, 'members']);
    Route::get('/searchgroup/{id}', [GroupController::class, 'searchgroup']);
    Route::post('/group/{id}/settings', [GroupController::class, 'updateSettings']);
    Route::post('/group/{id}/admins', [GroupController::class, 'addAdmin']);
    Route::delete('/group/{id}/admins/remove', [GroupController::class, 'removeAdmin']);
    Route::delete('/group/{id}/users/remove', [GroupController::class, 'removeUser']);
    Route::post('/group/{id}/users', [GroupController::class, 'addUser']);
    Route::post('/group/message/send', [GroupController::class, 'sendMessage']);
    Route::post('/group/file/send', [GroupController::class, 'uploadFile']);
    Route::get('/group/messages', [GroupController::class, 'getMessages']);
    Route::post('/group/message/reaction/add', [GroupController::class, 'addReaction']);
    Route::delete('/group/message/reaction/remove/{id}', [GroupController::class, 'removeReaction']);
    Route::get('/group/Notifications', [GroupController::class, 'getNotifications']);
    Route::delete('/group/delete/{id}', [GroupController::class, 'deletegroup']);
    
    
    //group public chat routes
    Route::get('/groups/public', [GroupPublicController::class, 'index']);
    Route::get('/conversations', [GroupPublicController::class, 'conversations']);
    Route::get('/group/messages/{id}', [GroupPublicController::class, 'getMessages']);
    Route::post('/create/group/public', [GroupPublicController::class, 'create']);
    Route::get('/group/Join/{id}', [GroupPublicController::class, 'join']);
    Route::post('/group/sent', [GroupPublicController::class, 'sendmessage']);
    Route::post('/group/sent/file', [GroupPublicController::class, 'uploadFile']);
    Route::delete('/group/message/delete/{id}', [GroupPublicController::class, 'deleteMessage']);
    Route::post('/group/addReaction/{id}', [GroupPublicController::class, 'addReaction']);
    Route::delete('/group/removeReaction/{id}', [GroupPublicController::class, 'removeReaction']);


    //call
    // Route::post('/start-call', [CallController::class, 'startCall']);
    // Route::post('/answer-call', [CallController::class, 'answerCall']);
    // Route::post('/end-call', [CallController::class, 'endCall']);

});
