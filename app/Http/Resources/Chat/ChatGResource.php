<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatGResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *@param \Illuminate\Http\Request $request
     *@return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        date_default_timezone_set("America/bogota");

        if($this->resource ->chat_group_id){

        return [
            

            "friend_first" => null
                   ,
            "friend_second" => $this->resource->second_user ?  $this->resource->second_user != auth('api')-> user()->id ?
                                               
                            [
                                "id" => $this->resource->SecondUser->id,
                                "full_name" => $this->resource->SecondUser->name,
                                "avatar" => $this->resource->SecondUser->usr_avatar ? $this->resource->SecondUser->usr_avatar:  "non-avatar.png",
                            ] :NULL
                        
                        :NULL,
                     "group_chat" => $this->resource ->chat_group_id ? [
                        "id" => $this->resource->ChatGroup->id,
                        "full_name" => $this->resource->ChatGroup->name,
                        "avatar" =>  $this->resource->ChatGroup->avatar ? $this->resource->ChatGroup->avatar:  "non-avatar.png",
                        "last_message" => $this->resource->ChatGroup->last_message,
                        "last_message_is_my" => $this->resource->ChatGroup->last_message_user ? $this->resource->ChatGroup->last_message_user  === auth('api')->user()->id:  "non-avatar.png",
                        "last_time" => $this->resource->ChatGroup -> last_time_created_at,
                        "count_message" =>$this->resource->ChatGroup->getCountMessages(auth('api')->user()->id),
                        ]  : NULL,
                    "uniqd" => $this->resource->uniqd,
                    "is_active" => false,
                    "last_message" => $this->resource->last_message,
                    "last_message_is_my" => $this->resource->last_message_user ? $this->resource->last_message_user  === auth('api')->user()->id:  "non-avatar.png",
                    "last_time" => $this->resource-> last_time_created_at,
                    "count_message" => $this->resource->getCountMessages(auth('api')->user()->id),

        ];}
        else{

            return [
            

                "friend_first" => $this->resource -> first_user != auth('api')-> user()->id ?
                        [
                            "id" =>$this->resource->FirstUser->id,
                            "full_name" => $this->resource->FirstUser->name,
                            "avatar" => $this->resource->FirstUser->usr_avatar ? $this->resource->FirstUser->usr_avatar:  "non-avatar.png",
                        ]: NULL,
                "friend_second" => $this->resource->second_user ?  $this->resource->second_user != auth('api')-> user()->id ?
                                                   
                                [
                                    "id" => $this->resource->SecondUser->id,
                                    "full_name" => $this->resource->SecondUser->name,
                                    "avatar" => $this->resource->SecondUser->usr_avatar ? $this->resource->SecondUser->usr_avatar:  "non-avatar.png",
                                ] :NULL
                            
                            :NULL,
                         "group_chat" => $this->resource ->chat_group_id ? [
                            "id" => $this->resource->ChatGroup->id,
                            "full_name" => $this->resource->ChatGroup->name,
                            "avatar" =>  $this->resource->ChatGroup->avatar ? $this->resource->ChatGroup->avatar:  "non-avatar.png",
                            "last_message" => $this->resource->ChatGroup->last_message,
                            "last_message_is_my" => $this->resource->ChatGroup->last_message_user ? $this->resource->ChatGroup->last_message_user  === auth('api')->user()->id:  "non-avatar.png",
                            "last_time" => $this->resource->ChatGroup -> last_time_created_at,
                            "count_message" =>$this->resource->ChatGroup->getCountMessages(auth('api')->user()->id),
                            ]  : NULL,
                        "uniqd" => $this->resource->uniqd,
                        "is_active" => false,
                        "last_message" => $this->resource->last_message,
                        "last_message_is_my" => $this->resource->last_message_user ? $this->resource->last_message_user  === auth('api')->user()->id:  "non-avatar.png",
                        "last_time" => $this->resource-> last_time_created_at,
                        "count_message" => $this->resource->getCountMessages(auth('api')->user()->id),
    
            ];
        }
    }
}
