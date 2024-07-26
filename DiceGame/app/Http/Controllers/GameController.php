<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function throwDices(Request $request, $id){
        // find and verify user
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }
    
        if ($request->user()->id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
    
        // throw dice
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
        $result = ($dice1 + $dice2) === 7;

        //create game
        $game = Game::create([
            'user_id' => $user->id,
            'dice1' => $dice1,
            'dice2' => $dice2,
            'result' => $result,
        ]);

        return response()->json([
            'message' => 'Game created successfully',
            'game' => [
                'nickname' => $user->nickname,
                'game' => $game,
                'message' => $result ? 'You won!' : 'You lost.',
            ]
        ]);
    }

    public function getGames($id){
    
    // Finding user
    $user = User::find($id);

    // Amount of games per player 
    $games = $user->games; 

    // Mapping games
    $gamesData = $games->map(function ($game) {
     return [
      'id' => $game->id,
      'dice1' => $game->dice1,
      'dice2' => $game->dice2,
      'result' => $game->result,
     ];
    });

      return response()->json([
       'status' => true,
       'games' => $gamesData,
      ]);
    }

    public function deleteGames($id){
        
        $user = User::find($id);

        $user->games()->delete();

        return response()->json([
         'message' => 'Succesfully deleted'
        ]);
    }
    
}
