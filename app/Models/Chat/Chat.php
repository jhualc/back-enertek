<?php

namespace App\Models\Chat;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Chat extends Model
{
    
    use SoftDeletes;
    protected $fillable = [
    
        "from_user_id",
        "chat_room_id",
        "chat_group_id",
        "message",
        "chat_file_id",
        "read_at"
    ];

    public function setCreatedAtAtribute($value){
    
        date_default_timezone_set("America/Bogota");
        $this->attribute["created_at"] = Carbon::now();
    }

    public function setUpdateAtAtribute($value){
    
        date_default_timezone_set("America/Bogota");
        $this->attribute["created_at"] = Carbon::now();
    }

    public function FromUser(){
        
        return $this->belongsTo(User::class, "from_user_id");
    }
    public function ChatRoom(){
        
        return $this->belongsTo(ChatRoom::class, "chat_room_id");
    }
    public function ChatGroup(){
        
        return $this->belongsTo(ChatGroup::class, "chat_group_id");
    }
    public function ChatFile(){
        
        return $this->belongsTo(ChatFile::class, "chat_file_id");
    }
}
