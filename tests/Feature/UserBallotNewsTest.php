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
  
  public function setUp() {
    parent::setUp();
    
    $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());
  }

  public function testGetBallotNews()
  {
    // Arrange
    $user = factory(\App\DataLayer\User::class)->create();
    
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $user->id,
      'congressional_district' => 1,
      'state_legislative_district' => 13,
      'state_house_district' => 7,
      'county' => 'Jefferson'
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
      'district_identifier' => $ballot->congressional_district,
      'ballotpedia_url' => 'https://www.google.com',
      'website_url' => 'https://www.yahoo.com',
      'donate_url' => 'https://www.redcross.com',
      'facebook_profile' => 'https://www.facebook.com',
      'twitter_handle' => 'someTwitterHandle',
      'data_source_id' => $datasource->id
    ]);

    $candidate2 = Candidate::createOrUpdate([
      'name' => 'Terrance Howard',
      'election_id' => $election->id,
      'consolidated_candidate_id' => null,
      'party_affiliation' => 'Democratic',
      'election_status' => 'On The Ballot',
      'office' => 'Senate',
      'office_level' => 'State',
      'is_incumbent' => 1,
      'district_type' => 'State Legislative (Upper)',
      'district' => 'Washington DC',
      'district_identifier' => $ballot->state_legislative_district,
      'ballotpedia_url' => 'https://www.google.com',
      'website_url' => 'https://www.yahoo.com',
      'donate_url' => 'https://www.redcross.com',
      'facebook_profile' => 'https://www.facebook.com',
      'twitter_handle' => 'someTwitterHandle',
      'data_source_id' => $datasource->id
    ]);

    $news1 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate->consolidated_candidate_id,
      'publish_date' => '2018-10-12',
    ]);

    $news2 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate2->consolidated_candidate_id,
      'publish_date' => '2018-6-1',
    ]);

    // Act
    $response = $this->actingAs($user)
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
    $user = factory(\App\DataLayer\User::class)->create();
    
    $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $user->id,
      'congressional_district' => 1,
      'state_legislative_district' => 13,
      'state_house_district' => 7,
      'county' => 'Jefferson',
      'state_abbreviation' => 'DE'
    ]);

    $ballot2 = factory(\App\DataLayer\Ballot\Ballot::class)->create([
      'user_id' => $user->id,
      'congressional_district' => 4,
      'state_legislative_district' => 12,
      'state_house_district' => 8,
      'county' => 'Jefferson',
      'state_abbreviation' => 'VA'
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

    $election2 = $this->election_repository->save(ElectionLoader::create([
      'name'=>'Some Other State Election',
      'state_abbreviation'=>$ballot2->state_abbreviation,
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
      'district_identifier' => $ballot->congressional_district,
      'ballotpedia_url' => 'https://www.google.com',
      'website_url' => 'https://www.yahoo.com',
      'donate_url' => 'https://www.redcross.com',
      'facebook_profile' => 'https://www.facebook.com',
      'twitter_handle' => 'someTwitterHandle',
      'data_source_id' => $datasource->id
    ]);

    $candidate2 = Candidate::createOrUpdate([
      'name' => 'King James III',
      'election_id' => $election2->id,
      'consolidated_candidate_id' => null,
      'party_affiliation' => 'Democratic',
      'election_status' => 'On The Ballot',
      'office' => 'Senate',
      'office_level' => 'State',
      'is_incumbent' => 1,
      'district_type' => 'State Legislative (Upper)',
      'district' => 'Washington DC',
      'district_identifier' => $ballot2->state_legislative_district,
      'ballotpedia_url' => 'https://www.google.com',
      'website_url' => 'https://www.yahoo.com',
      'donate_url' => 'https://www.redcross.com',
      'facebook_profile' => 'https://www.facebook.com',
      'twitter_handle' => 'someTwitterHandle',
      'data_source_id' => $datasource->id
    ]);

    $news1 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate->consolidated_candidate_id,
      'publish_date' => '2018-10-12',
    ]);

    $news2 = factory(\App\DataLayer\News::class)->create([
      'candidate_id' => $candidate2->consolidated_candidate_id,
      'publish_date' => '2018-6-1',
    ]);

    // Act
    $response = $this->actingAs($user)
      ->get('/api/users/me/ballots/'.$ballot->id.'/news');

    $response2 = $this->actingAs($user)
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
