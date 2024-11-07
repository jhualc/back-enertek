<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTController;
use App\Http\Controllers\User\ProfileUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Definir la ruta 'login' para manejar redirecciones de autenticación fallida en API
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated. Please log in.'], 401);
})->name('login');
