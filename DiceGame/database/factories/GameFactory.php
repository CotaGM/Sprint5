<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dice1 = $this->faker->numberBetween(1, 6);
        $dice2 = $this->faker->numberBetween(1, 6);
        $result = $dice1 + $dice2 === 7;
       
        return [
            'user_id' => User::factory(), 
            'dice1' => $dice1,
            'dice2' => $dice2,
            'result' => $result,
        ];
    }
    }

