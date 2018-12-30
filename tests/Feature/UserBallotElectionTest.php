<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\DataLayer\Election\Election;
use App\DataLayer\Candidate\Candidate;

class BallotElection extends TestCase
{
  use RefreshDatabase;

  public function testGetBallotElections()
  {
    // Arrange
    $user = factory(\App\DataLayer\User::class)->create();
    
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $user->id,
      'congressional_district' => 12
    ]);

    $datasource = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $election = Election::createOrUpdate([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$datasource->id,
      'consolidated_election_id'=>null
    ]);

    $candidate = Candidate::createOrUpdate([
      'name' => 'John Doe',
      'election_id' => $election->consolidated_election_id,
      'consolidated_candidate_id' => null,
      'party_affiliation' => 'Libertarian',
      'election_status' => 'On The Ballot',
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => 12,
      'ballotpedia_url' => 'https://www.google.com',
      'website_url' => 'https://www.yahoo.com',
      'donate_url' => 'https://www.redcross.com',
      'facebook_profile' => 'https://www.facebook.com',
      'twitter_handle' => 'someTwitterHandle',
      'data_source_id' => $datasource->id
    ]);

    // Act
    $response = $this->actingAs($user)
      ->get('/api/users/me/ballots/'.$ballot->id.'/elections');

    // Assert
    $response->assertJsonCount(1);

    $response->assertJson([[
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
    ]]);
  }
}
