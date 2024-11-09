<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class JWTController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function getAllUsers (Request $request)
    {
        $user = User::whereNull('deleted_at')->get();
        return response()->json([
         'message' => 'Respuesta Ok',
         'user' => $user
         ], 201);
    }
    /**
     * Register user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:6|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'usr_empresa' => 'required|string',
            'usr_cargo' => 'required|string',
            'usr_perfil' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'User error',
                'estado' => 400,
                'error' => $validator -> errors()
            ], 201);
        }

        $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'usr_empresa' => $request->usr_empresa,
                'usr_cargo' => $request->usr_cargo,
                'usr_perfil' => $request->usr_perfil
            ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamenete',
            'user' => $user
        ], 201);
    }

    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name' => 'string|min:6|max:100',
        'email' => 'string|email|max:100|unique:users,email,' . $id, // Ignorar el email actual
        'password' => 'nullable|string|min:6',
        'usr_empresa' => 'string',
        'usr_cargo' => 'string',
        'usr_perfil' => 'string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'estado' => 400,
            'error' => $validator->errors()
        ], 400);
    }

    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->name = $request->name ?? $user->name;
    $user->email = $request->email ?? $user->email;
    $user->password = $request->password ? Hash::make($request->password) : $user->password;
    $user->usr_empresa = $request->usr_empresa ?? $user->usr_empresa;
    $user->usr_cargo = $request->usr_cargo ?? $user->usr_cargo;
    $user->usr_perfil = $request->usr_perfil ?? $user->usr_perfil;
    $user->save();

    return response()->json([
        'message' => 'Usuario actualizado exitosamente',
        'user' => $user
    ], 200);
}

public function delete($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    $user->delete(); // Eliminación lógica si SoftDeletes está habilitado
    return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
}

public function destroyMultiple(Request $request)
{
    try {
        // Registrar los datos recibidos en el log para revisión
        \Log::info('Datos recibidos: ' . json_encode($request->all()));

        // Validar que cada 'user_id' existe en la tabla 'users'
        $validatedData = $request->validate([
            '*.id' => 'required|exists:users,id',
        ]);

        \Log::info('Datos validados: ' . json_encode($validatedData));

        // Obtener solo los IDs de usuario a partir de los datos validados
        $ids = collect($validatedData)->pluck('id')->all();

        \Log::info('Usuarios a eliminar: ' . implode(', ', $ids));

        // Eliminar usuarios (eliminación lógica si SoftDeletes está habilitado)
        User::whereIn('id', $ids)->delete();

        \Log::info('Usuarios eliminados');

        // Retornar una respuesta exitosa con los IDs eliminados
        return response()->json([
            'message' => 'Usuarios eliminados exitosamente',
            'eliminados' => $ids
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Manejar errores de validación y devolver detalles
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $e->errors(),
            'user_id_recibidos' => $request->all()
        ], 422);

    } catch (\Exception $e) {
        // Log y manejar cualquier otro error
        \Log::error('Error al eliminar usuarios: ' . $e->getMessage());

        return response()->json([
            'message' => 'Ocurrió un error al intentar eliminar los usuarios',
            'error' => $e->getMessage(),
            'user_id_recibidos' => $request->all()
        ], 500);
    }
}



    /**
     * login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        
        if ($validator->fails()) {
            print("ingreso error autenticador");
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
      
        return response()->json([
            'access_token' => $token, 
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                    "name"=> auth('api')->user()->name,
                    "username" => auth('api')->user()->username,
                    "email"=> auth('api')->user()->email,
                    "avatar"=> auth('api')->user()->usr_avatar,
                    "perfil"=> auth('api')->user()->usr_perfil,
                    "id"=> auth('api')->user()->id
                ]
        ]);
    }//
}
