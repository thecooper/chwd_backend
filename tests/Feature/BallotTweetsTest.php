<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use App\DataLayer\Candidate\Candidate;
use App\DataLayer\DataSource\DatasourceDTO;

use App\BusinessLogic\ElectionLoader;
use App\BusinessLogic\Models\Tweet;
use App\BusinessLogic\Models\TwitterUser;
use App\BusinessLogic\Models\TwitterEntity;
use App\BusinessLogic\Models\EntityUrl;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\ElectionFragmentCombiner;

class BallotTweetsTest extends TestCase
{
    use RefreshDatabase;

    private $election_repository;
    private $user;
    private $ballot;
    private $datasource;
    private $datasource_priority;
    private $election;

    public function setUp() {
      parent::setUp();
      
      $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());

      $this->user = factory(\App\DataLayer\User::class)->create();

      $this->ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $this->user->id,
        'congressional_district' => 1,
        'state_legislative_district' => 13,
        'state_house_district' => 7,
        'county' => 'Jefferson',
      ]);

      $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

      $this->datasource = DatasourceDTO::create($datasource_model);

      factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
        'data_source_id' => $this->datasource->id,
        'priority' => 1,
        'destination_table' => 'elections'
      ]);

      $this->election = $this->election_repository->save(ElectionLoader::create([
        'name' => 'Some State Election',
        'state_abbreviation' => $this->ballot->state_abbreviation,
        'primary_election_date' => '2020-11-6',
        'general_election_date' => '2020-11-7',
        'runoff_election_date' => '2020-11-8',
        'data_source_id' => $this->datasource->id,
        'election_id' => null,
      ]), $this->datasource->id);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBallotGetTweets()
    {
      // Arrange
      $candidate1 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'office' => 'Senate',
        'office_level' => 'Federal',
        'is_incumbent' => 1,
        'district_type' => 'Congress',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->congressional_district
      ]);

      $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'party_affiliation' => 'Democratic',
        'election_status' => 'On The Ballot',
        'office' => 'Senate',
        'office_level' => 'State',
        'is_incumbent' => 1,
        'district_type' => 'State Legislative (Upper)',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->state_legislative_district
      ]);

      $candidate3 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'office' => 'Representitive',
        'office_level' => 'State',
        'is_incumbent' => 1,
        'district_type' => 'State Legislative (Lower)',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->state_house_district
      ]);

      $tweet1 = new Tweet();
      $tweet1->id = 1234;
      $tweet1->created_at = '2016-12-13';
      $tweet1->text = 'Some tweet text here!';
      $tweet1->source = 'Worldwide';
      $tweet1->entities = new TwitterEntity();
      $tweet1->entities->url = new EntityUrl();
      $tweet1->entities->url->url = 'url1';
      $tweet1->entities->url->expanded_url = 'url2';
      $tweet1->entities->url->display_url = 'url3';
      $tweet1->twitter_user = new TwitterUser();
      $tweet1->twitter_user->id = 1;
      $tweet1->twitter_user->screen_name = $candidate1->twitter_handle;

      $tweet2 = new Tweet();
      $tweet2->id = 12;
      $tweet2->created_at = '2022-1-18';
      $tweet2->text = 'Some tweet text here also!';
      $tweet2->source = 'USA';
      $tweet2->entities = new TwitterEntity();
      $tweet2->entities->url = new EntityUrl();
      $tweet2->entities->url->url = 'url4';
      $tweet2->entities->url->expanded_url = 'url5';
      $tweet2->entities->url->display_url = 'url6';
      $tweet2->twitter_user = new TwitterUser();
      $tweet2->twitter_user->id = 874112341;
      $tweet2->twitter_user->screen_name = $candidate2->twitter_handle;

      $tweet3 = new Tweet();
      $tweet3->id = 578125076304;
      $tweet3->created_at = '2011-11-11';
      $tweet3->text = 'Other tweet text here!';
      $tweet3->source = 'Your Livingroom';
      $tweet3->entities = new TwitterEntity();
      $tweet3->entities->url = new EntityUrl();
      $tweet3->entities->url->url = 'url7';
      $tweet3->entities->url->expanded_url = 'url8';
      $tweet3->entities->url->display_url = 'url9';
      $tweet3->twitter_user = new TwitterUser();
      $tweet3->twitter_user->id = 948571278723;
      $tweet3->twitter_user->screen_name = $candidate3->twitter_handle;

      $twitter_api_mock = \Mockery::mock('App\DataSources\TwitterDataSource');
      $twitter_api_mock->shouldReceive('get_tweets_by_handles')
        ->andReturn([
          $tweet1,
          $tweet2,
          $tweet3
        ]);

      $this->app->instance('App\DataSources\TwitterDataSource', $twitter_api_mock);

      // Act
      $response = $this->actingAs($this->user)
        ->get('/api/users/me/ballots/'.$this->ballot->id.'/tweets');

      // Assert

      $response->assertOk();

      $response->assertJsonCount(3);

      $response->assertJson([
        [
          "id" => 12
        ],
        [
          "id" => 1234
        ],
        [
          "id" => 578125076304
        ]
      ]);
  }

  public function testBallotTweetsGetCached()
    {
      // Arrange
      $candidate1 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'office' => 'Senate',
        'office_level' => 'Federal',
        'is_incumbent' => 1,
        'district_type' => 'Congress',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->congressional_district,
      ]);

      $tweet1 = new Tweet();
      $tweet1->id = 1234;
      $tweet1->created_at = '2016-12-13';
      $tweet1->text = 'Some tweet text here!';
      $tweet1->source = 'Worldwide';
      $tweet1->entities = new TwitterEntity();
      $tweet1->entities->url = new EntityUrl();
      $tweet1->entities->url->url = 'url1';
      $tweet1->entities->url->expanded_url = 'url2';
      $tweet1->entities->url->display_url = 'url3';
      $tweet1->twitter_user = new TwitterUser();
      $tweet1->twitter_user->id = 1;
      $tweet1->twitter_user->screen_name = $candidate1->twitter_handle;

      $twitter_api_mock = \Mockery::mock('App\DataSources\TwitterDataSource');
      $twitter_api_mock->shouldReceive('get_tweets_by_handles')
        ->andReturn([
          $tweet1
        ]);

      $this->app->instance('App\DataSources\TwitterDataSource', $twitter_api_mock);

      // Act
      $response = $this->actingAs($this->user)
        ->get('/api/users/me/ballots/'.$this->ballot->id.'/tweets');

      // Assert

      $response->assertOk();

      $response->assertJsonCount(1);

      $this->assertTrue(Cache::has('twitter_handle_' . strtolower($candidate1->twitter_handle)));
  }
}
