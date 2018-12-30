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
    
    public function testGetMe()
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

    public function testGetUsersReturns401IfNotAuthenticated()
    {
      $response = $this->get('/api/users');
      $response->assertStatus(401);
    }

    public function testGetMeReturns401IfNotAuthenticated()
    {
      $response = $this->get('/api/users/me');
      $response->assertStatus(401);
    }

    public function testGetUsersReturns401IfAuthenticationCantFindUser()
    {
      $preBase64EncodeCredentials = "someuser:p@ssw0rd";
      $encodedCredentials = base64_encode($preBase64EncodeCredentials);
      $authenticationHeader = "Basic " . $encodedCredentials;

      $response = $this
        ->withHeaders([
          'Authorization' => $authenticationHeader
        ])
        ->get('/api/users');
      $response->assertStatus(401);
    }

    public function testGetMeReturns401IfAuthenticationCantFindUser()
    {
      $preBase64EncodeCredentials = "someuser:p@ssw0rd";
      $encodedCredentials = base64_encode($preBase64EncodeCredentials);
      $authenticationHeader = "Basic " . $encodedCredentials;

      $response = $this
        ->withHeaders([
          'Authorization' => $authenticationHeader
        ])
        ->get('/api/users/me');
      $response->assertStatus(401);
    }
}
