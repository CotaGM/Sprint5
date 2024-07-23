<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');*/

//Test Route with postman
Route::get('/test', function () {
    return response()->json(['message' => 'Hello from Laravel API!']);
});

// Open Routes
Route::post('/players', [UserController::class, 'register']); //crea un jugador/a.
Route::post('/login', [UserController::class, 'login']); //login

// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function(){
    Route::get('/profile', [UserController::class, "profile"]); //
    Route::get('/refresh-token', [UserController::class, "refreshToken"]);
    Route::get('/logout', [UserController::class, "logout"]);
    Route::put('/players/{id}', [UserController::class, "updateUser"]);
});

// Player Routes
Route::group([
    'middleware' => ["auth:api"]
], function () {
    Route::post('/players/{id}/games', [GameController::class, 'playGame']);
});


//Route::get('/players', [UserController::class, 'getList']);
//Route::post('/players', [UserController::class, 'register']);


//Route::post('/players/{id}/games/', [PlayerController::class], "create game");//un jugador/a específico realiza un tirón de los dados.
//Route::delete('/players/{id}/games/', [PlayerController::class], "delete games");//elimina las tiradas del jugador/a.

//acceso administrador
//Route::get('/players', [PlayerController::class, 'getList']);//devuelve el listado de todos los jugadores/as del sistema con su porcentaje medio de éxitos 
//Route::get('/players/{id}/games', [PlayerController::class], "games list");//devuelve el listado de jugadas por un jugador/a.
//Route::get('/players/ranking', [PlayerController::class], "ranking");//devuelve el ranking medio de todos los jugadores/as del sistema.  Es decir, el porcentaje medio de logros.
//Route::get('/players/ranking/loser', [PlayerController::class], "looser");//devuelve al jugador/a con peor porcentaje de éxito.
//Route::get('/players/ranking/winner', [PlayerController::class], "winner");//devuelve al jugador/a con mejor porcentaje de éxito .

