<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

use App\Http\Resources\User\ProfileUserGeneralResource;

class ProfileUserController extends Controller
{
    public function __construct(){
        
        $this->middleware('auth:api');
    }

    public function profile_user(Request $request)
    {
        //echo "Ingreso al profile", $request;
        $user = auth('api')->user();
        $userModel =  User::findOrFail($user->id);
      //  echo "Ingreso al profile", $userModel;
        if($request->hasFile("imagen"))
        {
            if($userModel->avatar){
                Storage::delete($userModel->avatar);
            }
            $path = Storage::putFile('users',$request->file("imagen"));
            $request->request->add(["usr_avatar" => $path]);
        }
        $userModel->update($request->all());
        return response()->json(["message"=> 200, "user" => ProfileUserGeneralResource::make($userModel)]);
    }


    public function contactUsers(){



        $users = User::where('id','<>',auth('api')->user()->id)->orderBy('name','asc')->get();
        return response()->json(["users" => $users->map(function($user){
            return ProfileUserGeneralResource::make($user);
        }),
        
    ]);
    }

    public function contactUserSponsors(){

        // Se agrega un segundo where para filtrar por 'user_data_persona' igual a 1
        $users = User::where('id', '<>', auth('api')->user()->id)
                    //->where('usr_datos_personales', '=', 1) // Filtro adicional aquí
                     ->orderBy('name', 'asc')
                     ->get();
                        
        return response()->json([
            "users" => $users->map(function($user){
                return ProfileUserGeneralResource::make($user);
            }),
        ]);
    }
  

    public function setUserAuthorizationPersonalData(Request $request){
        
        $request->validate([
            'datosPersonales' => 'required|boolean', 
            'id' => 'required|integer'
        ]);

        

       

        $user = User::find( $request->id);


        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->usr_datos_personales = $request->datosPersonales;
        $user->save();

        return response()->json([
            'message' => 'Los datos personales han sido actualizados correctamente.',
            'user' => ProfileUserGeneralResource::make($user)
        ]);
    }

    public function getUser($id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json([
                'message' => 'Respuesta Ok',
                'user' => $user
            ], 200); // Utiliza el código 200 para una respuesta exitosa
        } else {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404); // Utiliza el código 404 cuando no se encuentra el usuario
        }
    }


}
