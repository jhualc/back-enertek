<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessageChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $chat;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    public function broadcastWith()
    {
        return [
            "id" => $this->chat->id,
            "sender" => [
                "id" => $this->chat->FromUser->id,
                "full_name" => $this->chat->FromUser->name,
                "avatar" => $this->chat->FromUser->usr_avatar  ? $this->chat->FromUser->usr_avatar:  "users/non-avatar.svg",
            ],
            "message" => $this->chat->message,
            // "filw"
            
            "read_at" => $this->chat->read_at,
            "time" => $this->chat->created_at->diffForHumans(),
            "created_at" => $this->chat->created_at,
        ];
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chat.room.'.$this->chat->ChatRoom->uniqd);
    }
}
