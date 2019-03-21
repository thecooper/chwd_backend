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
  private $user;
  private $datasource;
  private $datasource_priority;
  
  public function setUp() {
    parent::setUp();
    
    $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());

    $this->user = factory(\App\DataLayer\User::class)->create();

    $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $this->datasource = DatasourceDTO::create($datasource_model);

    $this->datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'data_source_id' => $this->datasource->id,
      'priority' => 1,
      'destination_table' => 'elections'
    ]);
  }

  protected function tearDown() {
    gc_collect_cycles();
  }
  
  public function testGetBallotElections()
  {
    // Arrange
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $this->user->id,
      'congressional_district' => 1,
      'state_legislative_district' => 12,
      'state_house_district' => 7,
      'county' => 'Jefferson',
    ]);

    $election = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'data_source_id'=>$this->datasource->id,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>null,
    ]), $this->datasource->id);

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'data_source_id'=>$this->datasource->id,
      'primary_election_date'=>'2050-11-06',
      'general_election_date'=>'2050-11-07',
      'runoff_election_date'=>null,
    ]), $this->datasource->id);
    
    $candidate = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => $ballot->congressional_district,
    ]);

    $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election2->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => $ballot->congressional_district,
    ]);

    // Act
    $response = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot->id.'/elections');

    // Assert
    $response->assertJsonCount(2);

    $response->assertJson([
      "upcoming_elections" => [
        [
          'name'=>'Some Other State Election',
          'state_abbreviation'=>$ballot->state_abbreviation,
          'primary_election_date'=>'2050-11-06',
          'general_election_date'=>'2050-11-07',
          'runoff_election_date'=>null,
        ]
      ],
      "past_elections" => [
        [
          'name'=>'Some State Election',
          'state_abbreviation'=>$ballot->state_abbreviation,
          'primary_election_date'=>'2018-11-06',
          'general_election_date'=>'2018-11-07',
          'runoff_election_date'=>null,
        ]
      ]
    ]);

    $this->assertTrue(!isset($response->candidates));
  }

  public function testGetElectionMultiBallot() {
    // Arrange
    $ballots = factory(\App\DataLayer\Ballot\Ballot::class, 2)->create([
      'user_id' => $this->user->id,
      'congressional_district' => 12
    ]);

    $ballot1 = $ballots[0];
    $ballot2 = $ballots[1];
    
    $election1 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot1->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
      'data_source_id'=>$this->datasource->id,
      'election_id'=>null
    ]), $this->datasource->id);

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>'2018-11-06',
      'general_election_date'=>'2018-11-07',
      'runoff_election_date'=>'2018-11-08',
      'data_source_id'=>$this->datasource->id,
      'election_id'=>null
    ]), $this->datasource->id);

    $candidate1 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election1->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => 12
    ]);

    $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election2->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => 12
    ]);

    // Act
    $response1 = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot1->id.'/elections');
    $response2 = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot2->id.'/elections');

    // Assert

    $response1->assertJson([
      "past_elections" => [
        [
          'name'=>$election1->name,
          'state_abbreviation'=>$election1->state_abbreviation,
          'primary_election_date'=>$election1->primary_election_date,
          'general_election_date'=>$election1->general_election_date,
          'runoff_election_date'=>$election1->runoff_election_date,
        ]
      ],
      "upcoming_elections" => []
    ]);

    $response2->assertJson([
      "past_elections" => [
        [
          'name'=>$election2->name,
          'state_abbreviation'=>$election2->state_abbreviation,
          'primary_election_date'=>$election2->primary_election_date,
          'general_election_date'=>$election2->general_election_date,
          'runoff_election_date'=>$election2->runoff_election_date,
        ]
        ]
    ]);
  }

  public function testGetElectionMultiBallotWithSameElection() {
    // Arrange
    $ballots = factory(\App\DataLayer\Ballot\Ballot::class, 2)->create([
      'user_id' => $this->user->id,
      'congressional_district' => 12
    ]);

    $ballot1 = $ballots[0];
    $ballot2 = $ballots[1];
    
    $election1 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot1->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$this->datasource->id,
      'election_id'=>null
    ]), $this->datasource->id);

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>null,
      'general_election_date'=>'2018-11-6',
      'runoff_election_date'=>null,
      'data_source_id'=>$this->datasource->id,
      'election_id'=>null
    ]), $this->datasource->id);

    $candidate1 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election1->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => $ballot1->congressional_district,
    ]);

    $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'name' => 'Jane Doe',
      'election_id' => $election2->id,
      'party_affiliation' => 'Libertarian',
      'election_status' => 'On The Ballot',
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => $ballot2->congressional_district,
    ]);

    // Act
    $response1 = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot1->id.'/elections');
    $response2 = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot2->id.'/elections');

    // Assert
    $response1->assertJson([
      "past_elections" => [
        [
          'name'=>'Some State Election',
          'state_abbreviation'=>$ballot1->state_abbreviation,
          'primary_election_date'=>'2018-11-06',
          'general_election_date'=>'2018-11-07',
          'runoff_election_date'=>'2018-11-08',
        ]
      ]
    ]);

    $response2->assertJson([
      "past_elections" => [
        [
          'name'=>'Some Other State Election',
          'state_abbreviation'=>$ballot2->state_abbreviation,
          'primary_election_date'=>null,
          'general_election_date'=>'2018-11-06',
          'runoff_election_date'=>null,
        ]
      ]
    ]);
  }
}
