<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'nickname' => 'Admin',
            'email' => 'hola@admin.com',
            'password' => Hash::make('Admin123!'), 
        ]);

        $players = User::factory(5)->create();

        $players->each(function($player) {
            Game::factory(10)->create(['user_id' => $player->id]); 
        });
    }
}