<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Game;
use Laravel\Passport\ClientRepository as PassportClientRepository;


class GameControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    /**
     * A basic feature test example.
     */


    public function test_example(): void
    {
         $response = $this->get('/');
 
         $response->assertStatus(200);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    protected function setUp(): void{
         parent::setUp();
 
         // Configurar el cliente de acceso personal para Passport
         $clientRepository = new PassportClientRepository();
         $clientRepository->createPersonalAccessClient(
             null, 'Test Personal Access Client', 'http://localhost/'
         );
    }

   
    #[\PHPUnit\Framework\Attributes\Test]
    public function throwing_dices_for_non_existent_user_returns_error(){
        $this->withoutExceptionHandling();

        // Create the user on the db
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Create a token for the user
        $userToken = $user->createToken('UserToken')->accessToken;

        // Act as a user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->postJson('/api/players/999/games'); 

        // Assert
        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'User not found',
                 ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_throw_dices(){
        $this->withoutExceptionHandling();

        // Create the user on the db
        $user = User::factory()->create();
        
        // Authenticate the user
        $this->actingAs($user);
        
        // Create a token for the user (assuming you are using Laravel Passport)
        $userToken = $user->createToken('UserToken')->accessToken;

        // Act as a user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->postJson('/api/players/' . $user->id . '/games');

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'game' => [
                         'nickname', // Assuming 'nickname' is correct, change to 'user_name' if needed
                         'game' => [
                             'id',
                             'user_id',
                             'dice1',
                             'dice2',
                             'result'
                         ],
                         'message',
                     ]
                 ]);

        $this->assertDatabaseHas('games', [
            'user_id' => $user->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_cannot_throw_dices_for_another_user(){
        // Create the users in the database
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Create a token for the user
        $userToken = $user->createToken('UserToken')->accessToken;

        // Act as the user and try to throw dices for another user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->postJson('/api/players/' . $anotherUser->id . '/games');

        // Assert
        $response->assertStatus(403)
                 ->assertJson([
                     'message' => 'Unauthorized',
                 ]);
    }

}