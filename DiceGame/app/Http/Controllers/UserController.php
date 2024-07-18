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
        $validator = Validator::make($request->all(), [
            'nickname' => 'required|string|unique:users,nickname|max:100',
            'email' => 'nullable|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'email' => $request->email,
            'nickname' => $request->nickname ?? 'Anonymous',
            'password' => Hash::make($request->password),
            'role' => 'player',
        ]);

        return response()->json($user, 200);
    }
}