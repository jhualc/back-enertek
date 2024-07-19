<?php

namespace App\Models\Chat;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ChatRoom extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "id",
        "first_user",
        "second_user",
        "chat_group_id",
        "last_at",
        "uniqd"
    ];

    public function setCreatedAtAtribute($value){
    
        date_default_timezone_set("America/Bogota");
        $this->attribute["created_at"] = Carbon::now();
    }

    public function setUpdateAtAtribute($value){
    
        date_default_timezone_set("America/Bogota");
        $this->attribute["created_at"] = Carbon::now();
    }

    public function FirstUser(){
    
        return $this->belongsTo(User::class, "first_user");
    }

    public function SecondUser(){
    
        return $this->belongsTo(User::class, "second_user");
    }

    public function ChatGroup(){
    
        return $this->belongsTo(ChatGroup::class, "chat_group_id");
    }

    public function Chats(){
    
        return $this->hasMany(Chat::class, "chat_room_id");
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
