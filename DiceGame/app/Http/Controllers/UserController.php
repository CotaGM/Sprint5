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