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
}