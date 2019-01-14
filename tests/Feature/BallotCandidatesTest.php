<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\DataLayer\Election\ElectionFragment;
use App\DataLayer\Candidate\Candidate;
use App\DataLayer\Candidate\ConsolidatedCandidate;
use App\DataLayer\DataSource\DatasourceDTO;

use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\ElectionFragmentCombiner;
use App\BusinessLogic\ElectionLoader;
use App\BusinessLogic\Repositories\UserBallotCandidateRepository;

class BallotCandidates extends TestCase
{
  use RefreshDatabase;

  private $election_repository;
  
  public function setUp() {
    parent::setUp();
    
    $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());
  }
  
  public function testGetBallotCandidates()
  {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();

      $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id,
        'congressional_district' => 1,
        'state_legislative_district' => 13,
        'state_house_district' => 7,
        'county' => 'Jefferson',
      ]);

      $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

      $datasource = DatasourceDTO::create($datasource_model);

      $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
        'data_source_id' => $datasource->id,
        'priority' => 1,
        'destination_table' => 'elections'
      ]);

      $election = $this->election_repository->save(ElectionLoader::create([
        'name' => 'Some State Election',
        'state_abbreviation' => $ballot->state_abbreviation,
        'primary_election_date' => '2018-11-6',
        'general_election_date' => '2018-11-7',
        'runoff_election_date' => '2018-11-8',
        'data_source_id' => $datasource->id,
        'election_id' => null,
      ]), $datasource);

      $candidate1 = Candidate::createOrUpdate([
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
        'data_source_id' => $datasource->id,
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
        'data_source_id' => $datasource->id,
      ]);

      $candidate3 = Candidate::createOrUpdate([
        'name' => 'Jane Doe',
        'election_id' => $election->id,
        'consolidated_candidate_id' => null,
        'party_affiliation' => 'Libertarian',
        'election_status' => 'On The Ballot',
        'office' => 'Representitive',
        'office_level' => 'State',
        'is_incumbent' => 1,
        'district_type' => 'State Legislative (Lower)',
        'district' => 'Washington DC',
        'district_identifier' => $ballot->state_house_district,
        'ballotpedia_url' => 'https://www.google.com',
        'website_url' => 'https://www.yahoo.com',
        'donate_url' => 'https://www.redcross.com',
        'facebook_profile' => 'https://www.facebook.com',
        'twitter_handle' => 'someTwitterHandle',
        'data_source_id' => $datasource->id,
      ]);

      // Act
      $response = $this->actingAs($user)
        ->get('/api/users/me/ballots/'.$ballot->id.'/candidates');

      // Assert

      $response->assertOk();

      $response->assertJsonCount(3);

      $response->assertJson([
        'Congress' => [
          'Senate' => [
            [
              'name' => 'John Doe',
              'election_id' => $election->id,
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
            ],
          ],
        ],
        'State Legislative (Upper)' => [
          'Senate' => [
            [
              'name' => 'Terrance Howard',
              'election_id' => $election->id,
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
            ],
          ],
        ],
        'State Legislative (Lower)' => [
          'Representitive' => [
            [
              'name' => 'Jane Doe',
              'election_id' => $election->id,
              'party_affiliation' => 'Libertarian',
              'election_status' => 'On The Ballot',
              'office' => 'Representitive',
              'office_level' => 'State',
              'is_incumbent' => 1,
              'district_type' => 'State Legislative (Lower)',
              'district' => 'Washington DC',
              'district_identifier' => $ballot->state_house_district,
              'ballotpedia_url' => 'https://www.google.com',
              'website_url' => 'https://www.yahoo.com',
              'donate_url' => 'https://www.redcross.com',
              'facebook_profile' => 'https://www.facebook.com',
              'twitter_handle' => 'someTwitterHandle',
            ],
          ],
        ],
      ]);
  }

  public function testSelectCandidate()
  {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();

      $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id,
        'congressional_district' => 1,
        'state_legislative_district' => 13,
        'state_house_district' => 7,
        'county' => 'Jefferson',
      ]);

      $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

      $datasource = DatasourceDTO::create($datasource_model);

      $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
        'data_source_id' => $datasource->id,
        'priority' => 1,
        'destination_table' => 'elections'
      ]);

      $election = $this->election_repository->save(ElectionLoader::create([
        'name' => 'Some State Election',
        'state_abbreviation' => $ballot->state_abbreviation,
        'primary_election_date' => '2018-11-6',
        'general_election_date' => '2018-11-7',
        'runoff_election_date' => '2018-11-8',
        'data_source_id' => $datasource->id,
        'election_id' => null,
      ]), $datasource);

      $candidate1 = Candidate::createOrUpdate([
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
        'data_source_id' => $datasource->id,
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
        'data_source_id' => $datasource->id,
      ]);

      $candidate3 = Candidate::createOrUpdate([
        'name' => 'Jane Doe',
        'election_id' => $election->id,
        'consolidated_candidate_id' => null,
        'party_affiliation' => 'Libertarian',
        'election_status' => 'On The Ballot',
        'office' => 'Representitive',
        'office_level' => 'State',
        'is_incumbent' => 1,
        'district_type' => 'State Legislative (Lower)',
        'district' => 'Washington DC',
        'district_identifier' => $ballot->state_house_district,
        'ballotpedia_url' => 'https://www.google.com',
        'website_url' => 'https://www.yahoo.com',
        'donate_url' => 'https://www.redcross.com',
        'facebook_profile' => 'https://www.facebook.com',
        'twitter_handle' => 'someTwitterHandle',
        'data_source_id' => $datasource->id,
      ]);

      $user_ballot_candidate_repository = new UserBallotCandidateRepository();

      // Act
      $response = $this->actingAs($user)
        ->put('/api/users/me/ballots/'.$ballot->id.'/candidates/' . $candidate1->consolidated_candidate_id);

      // Assert

      $response->assertStatus(201);
      
      $candidate_selected = $user_ballot_candidate_repository->candidate_is_selected($ballot->id, $candidate1->consolidated_candidate_id);
      $candidate2_not_selected = $user_ballot_candidate_repository->candidate_is_selected($ballot->id, $candidate2->consolidated_candidate_id);
      $candidate3_not_selected = $user_ballot_candidate_repository->candidate_is_selected($ballot->id, $candidate3->consolidated_candidate_id);

      $this->assertEquals($candidate_selected, true);
      $this->assertEquals($candidate2_not_selected, false);
      $this->assertEquals($candidate3_not_selected, false);
  }

  public function testSelectDifferentCandidate()
  {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();

      $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id,
        'congressional_district' => 1,
        'state_legislative_district' => 13,
        'state_house_district' => 7,
        'county' => 'Jefferson',
      ]);

      $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

      $datasource = DatasourceDTO::create($datasource_model);

      $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
        'data_source_id' => $datasource->id,
        'priority' => 1,
        'destination_table' => 'elections'
      ]);

      $election = $this->election_repository->save(ElectionLoader::create([
        'name' => 'Some State Election',
        'state_abbreviation' => $ballot->state_abbreviation,
        'primary_election_date' => '2018-11-6',
        'general_election_date' => '2018-11-7',
        'runoff_election_date' => '2018-11-8',
        'data_source_id' => $datasource->id,
        'election_id' => null,
      ]), $datasource);

      $candidate1 = Candidate::createOrUpdate([
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
        'data_source_id' => $datasource->id,
      ]);

      factory(\App\DataLayer\Candidate\UserBallotCandidate::class)->create([
        'user_ballot_id' => $ballot->id,
        'candidate_id' => $candidate1->consolidated_candidate_id
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
        'data_source_id' => $datasource->id,
      ]);

      $user_ballot_candidate_repository = new UserBallotCandidateRepository();

      // Act
      $response = $this->actingAs($user)
        ->put('/api/users/me/ballots/'.$ballot->id.'/candidates/' . $candidate2->consolidated_candidate_id);

      // Assert

      $response->assertStatus(201);

      $candidate1_selected = $user_ballot_candidate_repository->candidate_is_selected($ballot->id, $candidate1->consolidated_candidate_id);
      $candidate2_selected = $user_ballot_candidate_repository->candidate_is_selected($ballot->id, $candidate2->consolidated_candidate_id);

      $this->assertEquals($candidate1_selected, false);
      $this->assertEquals($candidate2_selected, true);
  }

  public function testUnselectCandidate()
  {
      // Arrange
      $user = factory(\App\DataLayer\User::class)->create();

      $ballot = factory(\App\DataLayer\Ballot\Ballot::class)->create([
        'user_id' => $user->id,
        'congressional_district' => 1,
        'state_legislative_district' => 13,
        'state_house_district' => 7,
        'county' => 'Jefferson',
      ]);

      $datasource_model = factory(\App\DataLayer\DataSource\DataSource::class)->create();

      $datasource = DatasourceDTO::create($datasource_model);

      $datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
        'data_source_id' => $datasource->id,
        'priority' => 1,
        'destination_table' => 'elections'
      ]);

      $election = $this->election_repository->save(ElectionLoader::create([
        'name' => 'Some State Election',
        'state_abbreviation' => $ballot->state_abbreviation,
        'primary_election_date' => '2018-11-6',
        'general_election_date' => '2018-11-7',
        'runoff_election_date' => '2018-11-8',
        'data_source_id' => $datasource->id,
        'election_id' => null,
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
        'data_source_id' => $datasource->id,
      ]);

      factory(\App\DataLayer\Candidate\UserBallotCandidate::class)->create([
        'user_ballot_id' => $ballot->id,
        'candidate_id' => $candidate->consolidated_candidate_id
      ]);

      $user_ballot_candidate_repository = new UserBallotCandidateRepository();

      // Act
      $response = $this->actingAs($user)
        ->delete('/api/users/me/ballots/'.$ballot->id.'/candidates/' . $candidate->consolidated_candidate_id);

      // Assert

      $response->assertStatus(202);

      $candidate_selected = $user_ballot_candidate_repository->candidate_is_selected($ballot->id, $candidate->consolidated_candidate_id);

      $this->assertEquals($candidate_selected, false);
  }
}
