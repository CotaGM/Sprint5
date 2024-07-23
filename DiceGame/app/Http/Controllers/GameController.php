<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
   
    public function throwDices(Request $request, int $id)
    {
        // find and verify user
        $user = Auth::user();
        $user = User::find($id);


        if ($request->user()->id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ]);
        }

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
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
                'user_name' => $user->nickname,
                'game' => $game,
                'message' => $result ? 'You won!' : 'You lost.',
            ]
        ]);
    }

}
