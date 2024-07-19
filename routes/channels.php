<?php

use BeyondCode\LaravelWebSockets\WebSockets\Channels\PrivateChannel;
use Illuminate\Support\Facades\Broadcast;
use app\Models\Chat\ChatRoom;
use Tymon\JWTAuth\Contracts\Providers\Auth;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
/*
Broadcast::channel('chat.room.{uniqd}', function ($user, $uniqd) {
    $chatroom = ChatRoom::where("uniqd",$uniqd)->first();
    if($chatroom->chat_group_id){
        return true;
    }else{
        return (int) $user->id === (int) $chatroom->first_user || (int) $user->id === (int) $chatroom->second_user;
    }
});
**/
Broadcast::channel('chat.room.{uniqd}', function ($user, $uniqd) {
    $chatroom = ChatRoom::where("uniqd",$uniqd)->first();
    if($chatroom->chat_group_id){
        return true;
    }else{
        return (int) $user->id === (int) $chatroom->first_user || (int) $user->id === (int) $chatroom->second_user;
    }
});

Broadcast::channel('chat.refresh.room.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('onlineusers', function($user){
    return [
        "id" => $user->id,
        "full_name" => $user->name.' '.$user->surname,
        "email" => $user->email,
        "avatar" => $user->avatar ? $this->chat->FromUser->usr_avatar:  "users/non-avatar.svg",
    ];
});
