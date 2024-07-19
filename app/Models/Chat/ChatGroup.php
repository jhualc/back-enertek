<?php

namespace App\Models\Chat;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "id",
        "name",
        "uniqd",
        "avatar"

    ];

    public function setCreatedAtAtribute($value){
    
        date_default_timezone_set("America/Bogota");
        $this->attribute["created_at"] = Carbon::now();
    }

    public function setUpdateAtAtribute($value){
    
        date_default_timezone_set("America/Bogota");
        $this->attribute["created_at"] = Carbon::now();
    }

    public function Chats(){
    
        return $this->hasMany(Chat::class, "chat_group_id");

    }
    
    public function ChatsRooms(){
    
        return $this->hasMany(ChatRoom::class, "chat_group_id");
        
    }

    
    public function getLAstMessageAttribute(){
        
        //Verificar si este this deberia ser un item
        $chat = $this->Chats->SortByDesc("id")->first();

        return $chat ? 
            $chat->message ? $chat->message: 'Archivo enviado'
            : NULL;
    }

    public function getLastMessageUserAttribute(){
        
        //Verificar si este this deberia ser un item
        $chat = $this->Chats->SortByDesc("id")->first();

        return $chat ? 
            $chat-> from_user_id
            : NULL;
    }

    public function getLAstTimeCreateAttribute(){
        
        //Verificar si este this deberia ser un item
        $chat = $this->Chats->SortByDesc("id")->first();

        return $chat ? 
            $chat->create_at->diffForHumans()
            : NULL;
    }

    public function getCountMessages($user){
    
        return $this->Chats->where("from_user_id","<>",$user)->where("read_at",NULL)->count();
    }
}
