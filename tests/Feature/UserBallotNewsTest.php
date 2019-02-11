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

class BallotNews extends TestCase
{
  use RefreshDatabase;

  private $election_repository;

  private $user;
  private $datasource;

  public function setUp() {
    parent::setUp();
    
    $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());

    $this->user = factory(\App\DataLayer\User::class)->create();

    $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $this->datasource = DatasourceDTO::create($datasource_model);

    $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'data_source_id' => $this->datasource->id,
      'priority' => 1,
      'destination_table' => 'elections'
    ]);
  }

  public function testGetBallotNews()
  {
    // Arrange
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $this->user->id,
      'congressional_district' => 1,
      'state_legislative_district' => 13,
      'state_house_district' => 7,
      'county' => 'Jefferson'
    ]);

    $election = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'election_id'=>null
    ]), $this->datasource);

    $candidate = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => $ballot->congressional_district
    ]);

    $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election->id,
      'office' => 'Senate',
      'office_level' => 'State',
      'is_incumbent' => 1,
      'district_type' => 'State Legislative (Upper)',
      'district' => 'Washington DC',
      'district_identifier' => $ballot->state_legislative_district
    ]);

    $news1 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate->id,
      'publish_date' => '2018-10-12',
    ]);

    $news2 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate2->id,
      'publish_date' => '2018-6-1',
    ]);

    // Act
    $response = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot->id.'/news');

    // Assert

    $response->assertOk();
    
    $response->assertJsonCount(2);

    $response->assertJson([
        [
          'url' => $news1->url,
          'thumbnail_url' => $news1->thumbnail_url,
          'title' => $news1->title,
          'description' => $news1->description,
        ],
        [
          'url' => $news2->url,
          'thumbnail_url' => $news2->thumbnail_url,
          'title' => $news2->title,
          'description' => $news2->description,
        ],
    ]);
  }

  public function testGetBallotNewsWithMultipleBallots()
  {
    // Arrange
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $this->user->id,
      'congressional_district' => 1,
      'state_legislative_district' => 13,
      'state_house_district' => 7,
      'county' => 'Jefferson',
    ]);

    $ballot2 = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $this->user->id,
      'congressional_district' => 4,
      'state_legislative_district' => 12,
      'state_house_district' => 8,
      'county' => 'Jefferson',
    ]);

    $election = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
    ]), $this->datasource);

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
    ]), $this->datasource);

    $candidate = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election->id,
      'office' => 'Senate',
      'office_level' => 'Federal',
      'is_incumbent' => 1,
      'district_type' => 'Congress',
      'district' => 'Washington DC',
      'district_identifier' => $ballot->congressional_district
    ]);

    $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $election2->id,
      'office' => 'Senate',
      'office_level' => 'State',
      'is_incumbent' => 1,
      'district_type' => 'State Legislative (Upper)',
      'district' => 'Washington DC',
      'district_identifier' => $ballot2->state_legislative_district,
    ]);

    $news1 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate->id,
      'publish_date' => '2018-10-12',
    ]);

    $news2 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate2->id,
      'publish_date' => '2018-6-1',
    ]);

    // Act
    $response = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot->id.'/news');

    $response2 = $this->actingAs($this->user)
      ->get('/api/users/me/ballots/'.$ballot2->id.'/news');
    
    // Assert

    $response->assertOk();
    
    $response->assertJsonCount(1);

    $response->assertJson([
        [
          'url' => $news1->url,
          'thumbnail_url' => $news1->thumbnail_url,
          'title' => $news1->title,
          'description' => $news1->description,
        ],
    ]);

    $response2->assertOk();
    
    $response2->assertJsonCount(1);

    $response2->assertJson([
        [
          'url' => $news2->url,
          'thumbnail_url' => $news2->thumbnail_url,
          'title' => $news2->title,
          'description' => $news2->description,
        ],
    ]);

    
  }
}
