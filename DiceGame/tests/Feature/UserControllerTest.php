<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;
    protected $anotherUser;
    protected $adminToken;
    
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }


    protected function setUp(): void
    {
        parent::setUp();

        // Personal access
        $clientRepository = new PassportClientRepository();
        $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', 'http://localhost'
        );

        //admin user for test
        $this->admin = User::create([
            'nickname' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
        ]);
        $this->admin->role = 'admin';
        $this->admin->save();
        
        $this->adminToken = $this->admin->createToken('AdminToken')->accessToken;
       
        //Player user for test
        $this->anotherUser = User::factory()->create([
            'nickname' => 'Player',
            'email' => 'player@example.com',
            'password' => bcrypt('player123'),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_register(){

        $response = $this->postJson('/api/players', [
            'nickname' => 'TestUser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'User registered successfully!',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_register_as_anonymous(){

        $response = $this->postJson('/api/players', [
            'email' => 'anonymous@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'User registered successfully!',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'anonymous@example.com',
            'nickname' => 'Anonymous',
        ]);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_update_their_nickname(){

        // Create the users in the database
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Create a token for the user
        $userToken = $user->createToken('UserToken')->accessToken;

       //Updating
        $response = $this->withHeaders([
          'Authorization' => 'Bearer ' . $userToken,
        ])->putJson('api/players/' . $user->id , [
          'nickname' => 'UpdatedNickname',
        ]);

       // Assert
        $response->assertStatus(200)
        ->assertJson([
          'status' => true,
          'message' => 'User nickname updated successfully',
          'user' => [
             'nickname' => 'UpdatedNickname',
          ],
        ]);

        $this->assertDatabaseHas('users', [
          'id' => $user->id,
          'nickname' => 'UpdatedNickname',
        ]);
    }

    public function a_user_can_update_another_user_nickname(){ 

        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $this->actingAs($user);


         // Create a token for the user
        $userToken = $user->createToken('UserToken')->accessToken;

       //Updating
        $response = $this->withHeaders([
          'Authorization' => 'Bearer ' . $anotherUser->id,
        ])->putJson('api/players/' . $anotherUser->id , [
          'nickname' => 'UpdatedNickname',
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'message' => 'Unauthorized',
                 ]);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_login(){

        // Create the user in the database
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password'),
        ]);

        // Attempt to login
        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password', // Pass the plain password here
        ]);

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token'
            ])
            ->assertJson([
                'status' => true,
                'message' => 'User logged in successfully',
            ]);
    }

    public function a_user_cannot_login_as_another_user(){

        // Create two users in the database
        $user = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => bcrypt('password1'),
        ]);

        $anotherUser = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => bcrypt('password2'),
        ]);

        $userToken = $user->createToken('UserToken')->accessToken;

        // Attempt to perform an action as user1 but using user2's endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->getJson('/api/profile/' . $anotherUser->id); // Assuming this endpoint fetches user profile

         // Assert the response should be 403 Forbidden with appropriate message
         $response->assertStatus(403)
         ->assertJson([
             'status' => false,
             'message' => 'Not allowed',
         ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_view_their_profile()
    {
    
        $user = User::factory()->create();

        $this->actingAs($user);

        $userToken = $user->createToken('UserToken')->accessToken;

        // authorization with token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->getJson('/api/profile');
    
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'User profile data',
                'user' => [
                    'id' => $user->id,
                ],
           ]);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_refresh_their_token(){

    // Create user
    $user = User::factory()->create();

    // Authentication
    $this->actingAs($user);

    // Create token
    $userToken = $user->createToken('UserToken')->accessToken;

    // refresh token
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $userToken,
    ])->getJson('/api/refresh-token');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'token',
        ])
        ->assertJson([
            'status' => true,
            'message' => 'Refresh token',
        ]);
    }
  
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_can_logout(){
    
        // Create user
        $user = User::factory()->create();

        // Create token
        $userToken = $user->createToken('UserToken')->accessToken;

        // refresh token
        $response = $this->withHeaders([
          'Authorization' => 'Bearer ' . $userToken,
        ])->getJson('/api/logout');


        $response->assertStatus(200)
            ->assertJson([
                 'status' => true,
                 'message' => 'User logged out',
             ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admins_can_view_ranking(){

        // Create user
        $admin = User::factory()->create([
              'role' => 'admin',
        ]);

        // Create token
        $adminToken = $admin->createToken('AdminToken')->accessToken;

        // Solicitation
        $response = $this->withHeaders([
          'Authorization' => 'Bearer ' . $adminToken,
        ])->getJson('/api/players/ranking');

        $response->assertStatus(200)
             ->assertJsonStructure([
                 'average_success_rate',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admins_can_view_loser(){
      // Create users 
    $user1 = User::factory()->create(['nickname' => 'WinnerUser']);
    $user2 = User::factory()->create(['nickname' => 'LoserUser']);
    
    // Create games
    Game::factory()->count(10)->create(['user_id' => $user1->id, 'result' => true]); 
    Game::factory()->count(10)->create(['user_id' => $user2->id, 'result' => false]); 

    // Create admin authenticate
    $admin = User::factory()->create(['role' => 'admin']);
    $adminToken = $admin->createToken('AdminToken')->accessToken;

    // Make request to get the winner
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $adminToken,
    ])->getJson('/api/players/ranking/loser');

    // Assert 
    $response->assertStatus(200)
        ->assertJsonStructure([
            'player' => [
                'id',
                'nickname',
                'success_rate',
            ],
        ]);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function admins_can_view_winner(){

    // Create users 
    $user1 = User::factory()->create(['nickname' => 'WinnerUser']);
    $user2 = User::factory()->create(['nickname' => 'LoserUser']);
    
    // Create games
    Game::factory()->count(10)->create(['user_id' => $user1->id, 'result' => true]); 
    Game::factory()->count(10)->create(['user_id' => $user2->id, 'result' => false]); 

    // Create admin authenticate
    $admin = User::factory()->create(['role' => 'admin']);
    $adminToken = $admin->createToken('AdminToken')->accessToken;

    // Make request to get the winner
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $adminToken,
    ])->getJson('/api/players/ranking/winner');

    // Assert 
    $response->assertStatus(200)
        ->assertJsonStructure([
            'player' => [
                'id',
                'nickname',
                'success_rate',
            ],
        ]);
    }
}
