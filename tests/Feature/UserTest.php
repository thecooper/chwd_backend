<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateUser()
    {
        $response = $this->json('POST', '/api/users', [
            'name' => 'John Smith',
            'email' => 'jsmith@example.com',
            'password' => 'letmein',
        ]);

        $response->assertStatus(201);
    }

    public function testGetAllUsers() {
        $user = factory(User::class)->create();

        $otherUsers = factory(User::class, 2)->create();

        $response = $this->actingAs($user)
            ->get('/api/users');

        $response->assertJsonCount(3);
    }
    
    public function testGetUser()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->get('/api/users/me');

        // print_r($response);
            
        $response->assertOk();
        
        $response->assertJson([
            'name'=>$user->name,
            'email'=>$user->email
        ]);
    }
}
