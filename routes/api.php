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
    Route::apiResource('/ordenes-trabajo', OrdenTrabajoController::class);
    Route::resource('/contrato-equipos', ContratoEquipoController::class);
    Route::resource('/equipos', EquipoController::class);


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
