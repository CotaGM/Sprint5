<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;


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