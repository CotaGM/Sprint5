<?php

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
Route::post('/players', [UserController::class, 'register']); // Crea un jugador/a
Route::post('/login', [UserController::class, 'login']); // Login

// Protected routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/refresh-token', [UserController::class, 'refreshToken']);
    Route::get('/logout', [UserController::class, 'logout']);
    Route::put('/players/{id}', [UserController::class, 'updateUser']);//modifica el nombre del jugador/a.

    // player
    Route::middleware(['can:is-player'])->group(function () {
        Route::post('/players/{id}/games', [GameController::class, 'throwDices']); // Un jugador/a específico realiza un tirón de los dados
        Route::delete('/players/{id}/games', [GameController::class, 'deleteGames']);//elimina las tiradas del jugador/a.
        Route::get('/players/{id}/games', [GameController::class, 'getGames']);//devuelve el listado de jugadas por un jugador/a.
    });

    // Admin
    Route::middleware(['can:is-admin'])->group(function () {
        Route::get('/players', [UserController::class, 'getPlayerList']);//devuelve el listado de todos los jugadores/as del sistema con su porcentaje medio de éxitos 
        Route::get('/players/ranking', [UserController::class, 'getRanking']); // Devuelve el ranking medio de todos los jugadores/as del sistema
        Route::get('/players/ranking/loser', [UserController::class, 'getLoser']);// devuelve al jugador/a con peor porcentaje de éxito
        Route::get('/players/ranking/winner', [UserController::class, 'getWinner']);// devuelve al jugador/a con mejor porcentaje de éxito 
    });
});