<?php

namespace App\Http\Controllers;

//use App\Models\EventoPersona;
use App\Models\EventoPersona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoPersonaController extends Controller
{
   
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getEventoPersona(){
       $eventoPersona = DB::table('vista_evento_persona')->get();
       return response()->json([
        'message' => 'Respuesta Ok',
        'eventopersona' => $eventoPersona
        ], 201);
    }

    public function getEventoPersonaByeventId($eve_id){
        $eventoPersona = EventoPersona::find($eve_id);
        return response()->json([
         'message' => 'Respuesta Ok',
         'eventopersona' => $eventoPersona
         ], 201);
     }

}
