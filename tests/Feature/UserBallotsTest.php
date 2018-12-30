<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Mockery;

const ENDPOINT = '/api/users/me/ballots';

class UserBallotsTest extends TestCase
{
    use RefreshDatabase;

    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateUserBallot()
    {
      $user = factory(\App\User::class)->create();

      $location = new \App\Models\Location();
      $location->address_line_1 = '1234 Someplace Drive';
      $location->address_line_2 = '';
      $location->city = 'Pawnee';
      $location->state = 'IN';
      $location->zip = '80245';
      $location->county = 'Jefferson';
      $location->congressional_district = '8';
      $location->state_legislative_district = '6';
      $location->state_house_district = '11';
      
      $geocodio_mock = \Mockery::mock('App\DataSources\GeocodioAPIDataSource');
      $geocodio_mock->shouldReceive('get_geolocation_information')
        ->andReturn($location);
      
      $this->app->instance('App\DataSources\GeocodioAPIDataSource', $geocodio_mock);
        
      $response = $this
        ->actingAs($user)
        ->json('POST', ENDPOINT, [
            'zip' => '80245'
        ]);

      $response
        ->assertStatus(201)
        ->assertJson([
          'address_line_1' => $location->address_line_1,
          'address_line_2' => $location->address_line_2,
          'city' => $location->city,
          'state_abbreviation' => $location->state,
          'zip' => $location->zip,
          'county' => $location->county,
          'congressional_district' => $location->congressional_district,
          'state_legislative_district' => $location->state_legislative_district,
          'state_house_district' => $location->state_house_district
        ]);
    }

    public function testCreateUserBallotNotAuthorized()
    {
      $user = factory(\App\User::class)->create();

      $location = new \App\Models\Location();
      $location->address_line_1 = '1234 Someplace Drive';
      $location->address_line_2 = '';
      $location->city = 'Pawnee';
      $location->state = 'IN';
      $location->zip = '80245';
      $location->county = 'Jefferson';
      $location->congressional_district = '8';
      $location->state_legislative_district = '6';
      $location->state_house_district = '11';
      
      $geocodio_mock = \Mockery::mock('App\DataSources\GeocodioAPIDataSource');
      $geocodio_mock->shouldReceive('get_geolocation_information')
        ->andReturn($location);
      
      $this->app->instance('App\DataSources\GeocodioAPIDataSource', $geocodio_mock);
        
      $response = $this
        ->json('POST', ENDPOINT, [
            'zip' => '80245'
        ]);

      $response->assertStatus(401);
    }

    public function testCreateUserBallotGeocodioReturnsError() {
      $user = factory(\App\User::class)->create();
      
      $geocodio_mock = \Mockery::mock('App\DataSources\GeocodioAPIDataSource');
      $geocodio_mock->shouldReceive('get_geolocation_information')
        ->andReturn(["error" => "response from the server (500) was not good: There was some kind of error"]);
      
      $this->app->instance('App\DataSources\GeocodioAPIDataSource', $geocodio_mock);
        
      $response = $this
        ->actingAs($user)
        ->json('POST', ENDPOINT, [
            'zip' => '80246'
        ]);

      $response->assertStatus(400);
    }

    public function testGetUserBallots()
    {
      // Arrange

      $user = factory(\App\User::class)->create();
      factory(\App\UserBallot::class, 5)->create([
        'user_id' => $user->id
      ]);

      // Act
      $response = $this->actingAs($user)
        ->get(ENDPOINT);

      // Assert
      $response
        ->assertOk()
        ->assertJsonCount(5);
    }

    public function testDeleteUserBallot() {
      // Arrange
      $user = factory(\App\User::class)->create();
      $userBallot = factory(\App\UserBallot::class)->create([
        'user_id'=>$user->id
      ]);

      // Act
      $response = $this
        ->actingAs($user)
        ->delete(ENDPOINT . '/' . $userBallot->id);

      // Assert
      $response->assertStatus(202);
    }

    public function testGetUserBallotsWithoutAuth() {
      // Act
      $response = $this->get(ENDPOINT);

      // Assert
      $response->assertStatus(401);
    }

    public function testDeleteUserBallotNotFound() {
      // Arrange
      $user = factory(\App\User::class)->create();

      // Act
      $response = $this
        ->delete(ENDPOINT . '/1');

      // Assert
      $response->assertStatus(404);
    }

    public function testDeleteUserBallotWithoutAuth() {
      // Arrange
      $user = factory(\App\User::class)->create();
      $userBallot = factory(\App\UserBallot::class)->create([
        'user_id'=>$user->id
      ]);

      // Act
      $response = $this
        ->delete(ENDPOINT . '/' . $userBallot->id);

      // Assert
      $response->assertStatus(401);
    }
}
