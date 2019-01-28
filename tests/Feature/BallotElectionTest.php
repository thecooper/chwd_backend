<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\ElectionFragmentCombiner;
use App\BusinessLogic\ElectionLoader;

use App\DataLayer\DataSource\DatasourceDTO;
use App\DataLayer\Election\ElectionFragment;
use App\DataLayer\Candidate\Candidate;

class BallotElectionTest extends TestCase
{
  use RefreshDatabase;

  private $election_repository;
  
  public function setUp() {
    parent::setUp();
    
    $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());
  }
  
  public function testGetBallotElections()
  {
    // Arrange
    $user = factory(\App\DataLayer\User::class)->create();
    
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $user->id,
      'congressional_district' => 12
    ]);

    $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $datasource = DatasourceDTO::create($datasource_model);

    $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'data_source_id' => $datasource->id,
      'priority' => 1,
      'destination_table' => 'elections'
    ]);

    $election = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$datasource->id,
      'election_id'=>null
    ]), $datasource);

    $candidate = Candidate::createOrUpdate([
      'name' => 'John Doe',
      'election_id' => $election->id,
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

    $this->assertTrue(!isset($response->candidates));
  }

  public function testGetElectionMultiBallot() {
    // Arrange
    $user = factory(\App\DataLayer\User::class)->create();
    
    $ballots = factory(\App\DataLayer\Ballot\Ballot::class, 2)->create([
      'user_id' => $user->id,
      'congressional_district' => 12
    ]);

    $ballot1 = $ballots[0];
    $ballot2 = $ballots[1];
    
    $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $datasource = DatasourceDTO::create($datasource_model);

    $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'data_source_id' => $datasource->id,
      'priority' => 1,
      'destination_table' => 'elections'
    ]);
    
    $election1 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot1->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$datasource->id,
      'election_id'=>null
    ]), $datasource);

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$datasource->id,
      'election_id'=>null
    ]), $datasource);

    $candidate1 = Candidate::createOrUpdate([
      'name' => 'John Doe',
      'election_id' => $election1->id,
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

    $candidate2 = Candidate::createOrUpdate([
      'name' => 'Jane Doe',
      'election_id' => $election2->id,
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
    $response1 = $this->actingAs($user)
      ->get('/api/users/me/ballots/'.$ballot1->id.'/elections');
    $response2 = $this->actingAs($user)
      ->get('/api/users/me/ballots/'.$ballot2->id.'/elections');

    // Assert
    $response1->assertJsonCount(1);

    $response1->assertJson([[
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot1->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
    ]]);

    $response2->assertJsonCount(1);

    $response2->assertJson([[
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
    ]]);
  }

  public function testGetElectionMultiBallotWithSameElection() {
    // Arrange
    $user = factory(\App\DataLayer\User::class)->create();
    
    $ballots = factory(\App\DataLayer\Ballot\Ballot::class, 2)->create([
      'user_id' => $user->id,
      'congressional_district' => 12
    ]);

    $ballot1 = $ballots[0];
    $ballot2 = $ballots[1];
    
    $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $datasource = DatasourceDTO::create($datasource_model);

    $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'data_source_id' => $datasource->id,
      'priority' => 1,
      'destination_table' => 'elections'
    ]);
    
    $election1 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot1->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$datasource->id,
      'election_id'=>null
    ]), $datasource);

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>null,
      'runoff_election_date'=>null,
      'data_source_id'=>$datasource->id,
      'election_id'=>null
    ]), $datasource);

    $candidate1 = Candidate::createOrUpdate([
      'name' => 'John Doe',
      'election_id' => $election1->id,
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

    $candidate2 = Candidate::createOrUpdate([
      'name' => 'Jane Doe',
      'election_id' => $election2->id,
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
    $response1 = $this->actingAs($user)
      ->get('/api/users/me/ballots/'.$ballot1->id.'/elections');
    $response2 = $this->actingAs($user)
      ->get('/api/users/me/ballots/'.$ballot2->id.'/elections');

    // Assert
    $response1->assertJsonCount(1);

    $response1->assertJson([[
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot1->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
    ]]);

    $response2->assertJsonCount(1);

    $response2->assertJson([[
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
    ]]);
  }
}
