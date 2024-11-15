<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTController;
use App\Http\Controllers\User\ProfileUserController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\EventoPersonaController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\ContratoEquipoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\TipoEquipoController;
use App\Http\Controllers\BateriaController;
use App\Http\Controllers\BateriaEquipoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\SedeClienteController;

//Route::resource('clientes', ClienteController::class);


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['middleware' => 'api'], function($router){
    Route::post('/register', [JWTController::class, 'register']);
    Route::post('/login', [JWTController::class, 'login']);
    Route::post('/logout', [JWTController::class, 'logout']);
    Route::post('/refresh', [JWTController::class, 'refresh']);
    Route::post('/profile', [JWTController::class, 'profile']);
    Route::get('/users', [JWTController::class, 'getAllUsers']);
    Route::get('/users/contact', [ProfileUserController::class, 'contactUsers']);
    Route::get('/users/sponsors', [ProfileUserController::class, 'contactUserSponsors']);
    Route::get('/getUser/{id}', [ProfileUserController::class, 'getUser']);
    Route::get('958331', [AgendaController::class, 'getAllAgenda']);
    Route::get('/persona', [PersonaController::class, 'getAllPersona']);
    Route::get('/grupo', [GroupController::class, 'getAllGropus']);
    Route::get('/personaById/{per_id}', [PersonaController::class, 'getPersonaById']);
    Route::get('/evento-persona', [EventoPersonaController::class, 'getEventoPersona']);
  //  Route::get('/evento-personaid/{eve_id}', [EventoPersonaController::class, 'getEventoPersonaByeventId']);
  //  Route::post('/start-chat', [ChatController::class, 'startChat']);
    Route::post('/chat-grupal', [ChatController::class, 'startGroupChat']);
    Route::post('/broadcasting/autho', [BroadcastController::class, 'authenticate']);
    Route::get('/sponsor', [SponsorController::class, 'getAllSponsor']);
    Route::post('/profile-user',  [ProfileUserController::class, 'profile_user']);
    Route::post('/personaDataAuthorization',  [ProfileUserController::class, 'setUserAuthorizationPersonalData']);
    //Route::apiResource('/ordenes-trabajo', OrdenTrabajoController::class);
    Route::resource('/contrato-equipo', ContratoEquipoController::class);
    Route::resource('/equipo', EquipoController::class);
    Route::resource('/marcas', MarcaController::class);
    Route::resource('/tipo-equipo', TipoEquipoController::class);
    Route::get('/baterias', [BateriaController::class, 'index']);
    Route::post('/baterias', [BateriaController::class, 'store']);
    Route::get('/baterias/{id}', [BateriaController::class, 'show']);
    Route::put('/baterias/{id}', [BateriaController::class, 'update']);
    Route::delete('/baterias/{id}', [BateriaController::class, 'destroy']);
    Route::delete('/baterias/delete-multiple', [BateriaController::class, 'destroyMultiple']);

    Route::resource('/baterias-equipo', BateriaEquipoController::class);
    Route::resource('/cliente', ClienteController::class);
    Route::resource('/contrato', ContratoController::class);
    Route::put('/user/{id}', [JWTController::class, 'update']);
    Route::delete('/user/{id}', [JWTController::class, 'delete']);
    Route::get('/cliente-sedes', [SedeClienteController::class, 'index']);
    Route::post('/cliente-sedes', [SedeClienteController::class, 'store']);
    Route::get('/cliente-sedes/{id}', [SedeClienteController::class, 'show']);
    Route::put('/cliente-sedes/{id}', [SedeClienteController::class, 'update']);
    Route::delete('/cliente-sedes/{id}', [SedeClienteController::class, 'destroy']);
    Route::delete('/cliente-sedes/delete-multiple', [SedeClienteController::class, 'destroyMultiple']);

    Route::delete('/marca/delete-multiple', [MarcaController::class, 'destroyMultiple']);
    Route::delete('/equipos/delete-multiple', [EquipoController::class, 'destroyMultiple']);
    Route::delete('/tipos-equipo/delete-multiple', [TipoEquipoController::class, 'destroyMultiple']);
    Route::delete('/users/delete-multiple', [JWTController::class, 'destroyMultiple']);



});



Route::group(['prefix' => 'chat'], function($router){

    Route::post('/start-chat', [ChatController::class, 'startChat']);
    Route::post('/start-chat-group', [ChatController::class, 'startChatgroup']);
    Route::post('/chat-room-paginate',  [ChatController::class, 'chatRoomPaginate']);
    Route::post('/list-my-chat-room', [ChatController::class, 'listMyChats']);
    Route::post('/send-message-txt', [ChatController::class, 'sendMessageText']);

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
