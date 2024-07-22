<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    
    //REGISTRATION (POST) [nickname, email, password] 
    public function register(Request $request)
    {

        $request -> validate([
            'nickname' => 'nullable|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if($request->nickname === null) {
            $request->merge(['nickname' => 'Anonymous']);
        }

        
        User::create([
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'player',
        ]);

        return response()->json(['message' => 'Successfully created user!',
        ], 201);
    }

    //LOGIN (POST) [email, password]
    public function login(Request $request){

    }

    //PROFILE (GET) (Auth Token - Header)
    public function profile(){

    }

    //REFRESH TOKEN (GET) (Auth Token -Header)
    public function refreshToken(){
  
    }

    //LOGOUT (GET) (Auth Token -Header)
    public function logout(){
    
    }
}