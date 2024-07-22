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
    public function register(Request $request)
    {

        $request -> validate([
            'nickname' => 'nullable|string|max:100|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        if($request->nickname === null) {
            $request->merge(['nickname' => 'Anonymous']);
        }

        User::create([
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'player',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered succesfully!',
        ], 201);
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
        'message' => $user,

    ]);

    }

    //REFRESH TOKEN (GET) (Auth Token -Header)
    public function refreshToken(){
  
    }

    //LOGOUT (GET) (Auth Token -Header)
    public function logout(){
    
    }
}