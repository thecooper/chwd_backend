<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Mockery;

use App\DataLayer\User;
use App\BusinessLogic\Models\Location;

const ENDPOINT = '/api/users/me/ballots';

class BallotsTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateBallot()
    {
      $user = factory(\App\DataLayer\User::class)->create();

      $location = new Location();
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

    public function testCreateBallotNotAuthorized()
    {
      $user = factory(\App\DataLayer\User::class)->create();

      $location = new Location();
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

    public function testCreateBallotGeocodioReturnsError() {
      $user = factory(\App\DataLayer\User::class)->create();
      
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

    public function testGetBallots()
    {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();
      factory(\App\DataLayer\Ballot\Ballot::class, 5)->create([
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

    public function testDeleteBallot() {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();
      $Ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id'=>$user->id
      ]);

      // Act
      $response = $this
        ->actingAs($user)
        ->delete(ENDPOINT . '/' . $Ballot->id);

      // Assert
      $response->assertStatus(202);
    }

    public function testGetBallotsWithoutAuth() {
      // Act
      $response = $this->get(ENDPOINT);

      // Assert
      $response->assertStatus(401);
    }

    public function testDeleteBallotNotFound() {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();

      // Act
      $response = $this
        ->delete(ENDPOINT . '/1');

      // Assert
      $response->assertStatus(404);
    }

    public function testDeleteBallotWithoutAuth() {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();
      $Ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id'=>$user->id
      ]);

      // Act
      $response = $this
        ->delete(ENDPOINT . '/' . $Ballot->id);

      // Assert
      $response->assertStatus(401);
    }

    public function testGetSingleBallot() {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();
      
      $ballot1 = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id
      ]);

      // Act
      $response1 = $this->actingAs($user)
        ->get(ENDPOINT . '/' . (string)$ballot1->id);

      // Assert
      $response1
        ->assertOk()
        ->assertJson([
          'id' => $ballot1->id,
          'user_id' => $ballot1->user_id,
          'address_line_1' => $ballot1->address_line_1,
          'address_line_2' => $ballot1->address_line_2,
          'city' => $ballot1->city,
          'zip' => $ballot1->zip,
          'county' => $ballot1->county,
          'state_abbreviation' => $ballot1->state_abbreviation,
          'congressional_district' => $ballot1->congressional_district,
          'state_legislative_district' => $ballot1->state_legislative_district,
          'state_house_district' => $ballot1->state_house_district
        ]);
    }

    public function testGetSingleBallotWithinMultiple() {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();
      
      $ballot1 = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id
      ]);

      $other_ballots = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id
      ]);

      $ballot2 = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id
      ]);
      
      // Act
      $response1 = $this->actingAs($user)
        ->get(ENDPOINT . '/' . (string)$ballot1->id);
      
      $response2 = $this->actingAs($user)
        ->get(ENDPOINT . '/' . (string)$ballot2->id);

      // Assert
      $response1
        ->assertOk()
        ->assertJson([
          'id' => $ballot1->id,
          'user_id' => $ballot1->user_id,
          'address_line_1' => $ballot1->address_line_1,
          'address_line_2' => $ballot1->address_line_2,
          'city' => $ballot1->city,
          'zip' => $ballot1->zip,
          'county' => $ballot1->county,
          'state_abbreviation' => $ballot1->state_abbreviation,
          'congressional_district' => $ballot1->congressional_district,
          'state_legislative_district' => $ballot1->state_legislative_district,
          'state_house_district' => $ballot1->state_house_district
        ]);

        $response2
        ->assertOk()
        ->assertJson([
          'id' => $ballot2->id,
          'user_id' => $ballot2->user_id,
          'address_line_1' => $ballot2->address_line_1,
          'address_line_2' => $ballot2->address_line_2,
          'city' => $ballot2->city,
          'zip' => $ballot2->zip,
          'county' => $ballot2->county,
          'state_abbreviation' => $ballot2->state_abbreviation,
          'congressional_district' => $ballot2->congressional_district,
          'state_legislative_district' => $ballot2->state_legislative_district,
          'state_house_district' => $ballot2->state_house_district
        ]);
    }
}
