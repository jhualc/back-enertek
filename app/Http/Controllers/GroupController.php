<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat\ChatGroup;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function getAllGropus(){
        $grupo = DB::table('chat_groups')->get();
        return response()->json([
            'message' => 'Grupos ok',
            'grupo' => $grupo,   
            ], 200);
    }
}
