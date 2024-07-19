<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat\ChatRoom;
use App\Http\Resources\Chat\ChatGResource;

class RefreshMyChatRoom implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $to_user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($to_user_id)
    {
        $this->to_user_id = $to_user_id;
        //
    }

    public function broadcastWith()
    {

      //  echo "Ref:", $this->to_user_id;
  
        $isGroup = ChatRoom::Where("chat_group_id", $this->to_user_id)
                                    ->count();
        
        if($isGroup){
            
            $chatrooms = ChatRoom::Where("chat_group_id", $this->to_user_id)
                               ->orderBy("last_at","desc")
                               ->get();
        }else{
        
        $chatrooms = ChatRoom::where("first_user", $this->to_user_id)
                                    ->orWhere("second_user", $this->to_user_id)
                                    ->orWhere("chat_group_id", $this->to_user_id)
                               ->orderBy("last_at","desc")
                               ->get();
        }
       
        date_default_timezone_set("America/Lima");
        return [
            "chatrooms" => $chatrooms->map(function($item){
                return [
                    "friend_first" => $item->first_user != $this->to_user_id ?
                    [
                        "id" => $item->FirstUser->id,
                        "full_name" => $item->FirstUser->name,
                        "avatar" => $item->FirstUser->avatar ? $item->FirstUser->usr_avatar:  "users/non-avatar.svg",
                    ] : NULL,
                    "friend_second" => $item->second_user ?
                        $item->second_user != $this->to_user_id ?
                        [
                            "id" => $item->SecondUser->id,
                            "full_name" => $item->SecondUser->name,
                            "avatar" => $item->SecondUser->avatar ? $item->SecondUser->usr_avatar:  "users/non-avatar.svg",
                        ] : NULL
                    : NULL,
                    "group_chat" => $item->chat_group_id ? [
                        "id" => $item->ChatGroup->id,
                        "name" => $item->ChatGroup->name,
                        "avatar" => $item->ChatGroup->avatar ? $item->ChatGroup->avatar:  "users/non-avatar-group.svg",

                        "last_message" => $item->ChatGroup->last_message,
                        "last_message_is_my" => $item->ChatGroup->last_message_user ?  $item->ChatGroup->last_message_user === $this->to_user_id : NULL,
                        "last_time" => $item->ChatGroup->last_time_created_at,
                        "count_message" => $item->ChatGroup->getCountMessages($this->to_user_id),
                    ] : NULL,
                    "uniqd" => $item->uniqd,
                    "is_active" => false,
                    "last_message" => $item->last_message,
                    "last_message_is_my" => $item->last_message_user ?  $item->last_message_user === $this->to_user_id : NULL,
                    "last_time" => $item->last_time_created_at,
                    "count_message" => $item->getCountMessages($this->to_user_id),
                ];
            }),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.refresh.room.'.$this->to_user_id);
    }
}
