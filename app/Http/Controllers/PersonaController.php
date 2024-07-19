<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllPersona(){
       $persona = DB::table('persona')->get();
       return response()->json([
        'message' => 'Respuesta Ok',
        'persona' => $persona
        ], 201);
    }

    public function getPersonaById($per_id){
    
        $persona = Persona::find($per_id);

        if(!$persona) return response()->json(['message' => 'No user found'], 404);

        return response()->json([
            'message' => 'Persona Ok',
            'code' => 200,
            'error' => false,
            'results' => $persona
        ], 200);

    }
}
