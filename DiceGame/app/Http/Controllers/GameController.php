<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function throwDice($id)
    {
        //find player
        $player = User::find($id);

        if (!$player) {
            return response()->json([
              'status' => false,
              'message' => 'User not found',
            ]);
          }

        //throw dice
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
        $winner= ($dice1 + $dice2) === 7;

        //create game
        $game = User::create([
            'player_id' => $player->id,
            'dice1' => $dice1,
            'dice2' => $dice2,
            'winner' => $winner,
        ]);

        return response()->json([
            'status' => true,
            'game' => $game,
            'message' => $winner ? 'You won!' : 'You lost.',
        ]);
    }


}
