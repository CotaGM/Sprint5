<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    
    //REGISTRATION (POST) [nickname, email, password] 
    public function register(Request $request){
        
      //validation
      $request -> validate([
        'nickname' => 'nullable|string|max:100|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|confirmed|min:6',
      ]);
        
        //Anonymous user
        if($request->nickname === null) {
            $request->merge(['nickname' => 'Anonymous']);
        }

        //create user
        User::create([
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'player',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered succesfully!',
        ]);
        
    }
    
    //UPDATE (PUT) [nickname, email, password] 
    public function updateUser(Request $request, $id){
        
      // Find user
      $user = User::find($id);
      if (!$user) {
        return response()->json([
          'status' => false,
          'message' => 'User not found',
        ]);
      }

      //Validation
      $request->validate([
      'nickname' => 'nullable|string|max:100|unique:users',
      ]);
  
      //Anonymous user
      $user->nickname = $request->nickname ?? 'Anonymous';

      //save nickname
      $user->save();

      return response()->json([
        'status' => true,
        'message' => 'User nickname updated successfully',
        'user' => $user,
      ]);

  }

    //LOGIN (POST) [email, password]
    public function login(Request $request){
     
        //Validation
        $request -> validate([
          'email' => 'required|email',
          'password' => 'required',
        ]);
  
        //Check user by "email" value
        $user = User::where("email", $request -> email)->first();
  
        //Check user by "password" value
        if(!empty($user)){
  
          if(Hash::check($request -> password, $user->password)){
              
              //Auth Token value
            $token = $user -> createToken("myToken")->accessToken;
            
            return response()->json([
              'status' => true,
              'message' => "User logged in succesfully",
              'token' => $token  
            ]);
            
          }else{
              return response()->json([
                  'status' => false,
                  'message' => "Password didn't match",  
              ]);
          }
       }else{
          return response()->json([
              'status' => false,
              'message' => "Invalid credentials",
          ]);
        }

      }

    //PROFILE (GET) (Auth Token - Header)
    public function profile(){

      $user = Auth::user();

      return response()->json([
        'status' => true,
        'message' => "User profile data",
        'user' => $user,
      ]);

    }

    //REFRESH TOKEN (GET) (Auth Token -Header)
    public function refreshToken(){
      $user = request () -> user (); //user data
      $token = $user -> createToken("newToken");

      $refreshToken = $token ->accessToken;
     
      return response()->json([
        'status' => true,
        'message' => "Refresh token",
        'token' => $refreshToken,
      ]);
    }

    //LOGOUT (GET) (Auth Token -Header)
    public function logout(){

      request()->user()->tokens()->delete();

      return response()->json([
        'status' => true,
        'message' => "User logged out",
      ]);

    }
    
    //RANKING(GET)
    public function getRanking()
    {
      $users = User::all();
      $totalGames = 0;
      $totalWins = 0;

      foreach ($users as $user) {
        $totalGames += $user->games->count();
        $totalWins += $user->games->where('result', true)->count();
      }

        $successRate = $totalGames > 0 ? ($totalWins / $totalGames) * 100 : 0;
  
      return response()->json([
        'average_success_rate' => $successRate
      ]);
    }
    
    //LOSER (GET)
    public function getLoser(){
      
      //get players
      $users = User::all();
      $lowestRate = PHP_INT_MAX; 
      $worstPlayer = null;

      foreach ($users as $user) {
      $totalGames = $user->games->count(); 
      $totalWins = $user->games->where('result', true)->count(); 

      // Percentage
      $successRate = $totalGames > 0 ? ($totalWins / $totalGames) * 100 : 0;

        // find the looser
        if ($successRate < $lowestRate) {
          $lowestRate = $successRate;
          $worstPlayer = $user;
        }
      }

      return response()->json([
       'player' => [
       'id' => $worstPlayer->id,
       'nickname' => $worstPlayer->nickname,
       'success_rate' => $lowestRate
       ]
      ]);

    }

    public function getWinner(){
    // get players
    $users = User::all();
    $highestRate = 0; 
    $bestPlayer = null;

    foreach ($users as $user) {
      $totalGames = $user->games->count(); 
      $totalWins = $user->games->where('result', true)->count(); 

      // Percentage
      $successRate = $totalGames > 0 ? ($totalWins / $totalGames) * 100 : 0;

      // find the winner
      if ($successRate > $highestRate) {
        $highestRate = $successRate;
        $bestPlayer = $user;
      }
    }

    return response()->json([
      'player' => [
      'id' => $bestPlayer->id,
      'nickname' => $bestPlayer->nickname,
      'success_rate' => $highestRate
      ]
    ]);
  }

  public function getPlayerList(){

    // get players
    $user = User::with('games')
      ->where('role', 'player') // Filtrar solo jugadores
      ->get();

    // Mapping all players
    $playersData = $user->map(function ($user) {
      $totalGames = $user->games->count();
      $totalWins = $user->games->where('result', true)->count();

      // Percentage
      $averageSuccessRate = $totalGames > 0 ? ($totalWins / $totalGames) * 100 : 0;

      return [
        'id' => $user->id,
        'nickname' => $user->nickname,
        'success_rate' => $averageSuccessRate
      ];
      
    });

    return response()->json([
        'players' => $playersData
    ]);
  }

}  
