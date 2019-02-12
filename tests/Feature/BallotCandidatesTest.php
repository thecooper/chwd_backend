<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\DataLayer\Election\ElectionFragment;
use App\DataLayer\Candidate\Candidate;
use App\DataLayer\DataSource\DatasourceDTO;

use App\BusinessLogic\ElectionLoader;
use App\BusinessLogic\ElectionFragmentCombiner;
use App\BusinessLogic\CandidateFragmentCombiner;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\Repositories\UserBallotCandidateRepository;
use App\BusinessLogic\Repositories\CandidateRepository;


class BallotCandidates extends TestCase
{
  use RefreshDatabase;

  private $election_repository;
  private $candidate_repository;
  
  private $user;
  private $ballot;
  private $datasource;
  private $datasource_priority;
  private $election;
  
  public function setUp() {
    parent::setUp();
    
    $this->election_repository = new ElectionRepository(new ElectionFragmentCombiner());
    $this->candidate_repository = new CandidateRepository(new CandidateFragmentCombiner());

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

    $this->datasource_priority = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'data_source_id' => $this->datasource->id,
      'priority' => 1,
      'destination_table' => 'elections'
    ]);

    $this->election = $this->election_repository->save(ElectionLoader::create([
      'name' => 'Some State Election',
      'state_abbreviation' => $this->ballot->state_abbreviation,
      'primary_election_date' => '2018-11-6',
      'general_election_date' => '2018-11-7',
      'runoff_election_date' => '2018-11-8',
      'data_source_id' => $this->datasource->id,
      'election_id' => null,
    ]), $this->datasource->id);
  }
  
  public function testGetBallotCandidates()
  {
      // Arrange
      $candidate1 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'election_status' => 'On The Ballot',
        'office' => 'Senate',
        'office_level' => 'Federal',
        'district_type' => 'Congress',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->congressional_district
      ]);

      $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'election_status' => 'On The Ballot',
        'office' => 'Senate',
        'office_level' => 'State',
        'district_type' => 'State Legislative (Upper)',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->state_legislative_district
      ]);

      $candidate3 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'office' => 'Representitive',
        'office_level' => 'State',
        'district_type' => 'State Legislative (Lower)',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->state_house_district
      ]);

      // Act
      $response = $this->actingAs($this->user)
        ->get('/api/users/me/ballots/'.$this->ballot->id.'/candidates');

      // Assert
      $response->assertOk();

      $response->assertJsonCount(3);

      $response->assertJson([
        'Congress' => [
          'Senate' => [
            [
              'name' => $candidate1->name,
              'election_id' => $this->election->id,
              'party_affiliation' => $candidate1->party_affiliation,
              'election_status' => $candidate1->election_status,
              'office' => $candidate1->office,
              'office_level' => $candidate1->office_level,
              'is_incumbent' => $candidate1->is_incumbent,
              'district_type' => $candidate1->district_type,
              'district' => $candidate1->district,
              'district_identifier' => $candidate1->district_identifier,
              'ballotpedia_url' => $candidate1->ballotpedia_url,
              'website_url' => $candidate1->website_url,
              'donate_url' => $candidate1->donate_url,
              'facebook_profile' => $candidate1->facebook_profile,
              'twitter_handle' => $candidate1->twitter_handle
            ],
          ],
        ],
        'State Legislative (Upper)' => [
          'Senate' => [
            [
              'name' => $candidate2->name,
              'election_id' => $this->election->id,
              'party_affiliation' => $candidate2->party_affiliation,
              'election_status' => $candidate2->election_status,
              'office' => $candidate2->office,
              'office_level' => $candidate2->office_level,
              'is_incumbent' => $candidate2->is_incumbent,
              'district_type' => $candidate2->district_type,
              'district' => $candidate2->district,
              'district_identifier' => $candidate2->district_identifier,
              'ballotpedia_url' => $candidate2->ballotpedia_url,
              'website_url' => $candidate2->website_url,
              'donate_url' => $candidate2->donate_url,
              'facebook_profile' => $candidate2->facebook_profile,
              'twitter_handle' => $candidate2->twitter_handle
            ],
          ],
        ],
        'State Legislative (Lower)' => [
          'Representitive' => [
            [
              'name' => $candidate3->name,
              'election_id' => $this->election->id,
              'party_affiliation' => $candidate3->party_affiliation,
              'election_status' => $candidate3->election_status,
              'office' => $candidate3->office,
              'office_level' => $candidate3->office_level,
              'is_incumbent' => $candidate3->is_incumbent,
              'district_type' => $candidate3->district_type,
              'district' => $candidate3->district,
              'district_identifier' => $candidate3->district_identifier,
              'ballotpedia_url' => $candidate3->ballotpedia_url,
              'website_url' => $candidate3->website_url,
              'donate_url' => $candidate3->donate_url,
              'facebook_profile' => $candidate3->facebook_profile,
              'twitter_handle' => $candidate3->twitter_handle,
            ],
          ],
        ],
      ]);
  }

  public function testSelectCandidate()
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
        'district_identifier' => $this->ballot->state_house_district,
      ]);

      $user_ballot_candidate_repository = new UserBallotCandidateRepository();

      // Act
      $response = $this->actingAs($this->user)
        ->put('/api/users/me/ballots/'.$this->ballot->id.'/candidates/' . $candidate1->id);

      // Assert
      $response->assertStatus(201);
      
      $candidate_selected = $user_ballot_candidate_repository->candidate_is_selected($this->ballot->id, $candidate1->id);
      $candidate2_not_selected = $user_ballot_candidate_repository->candidate_is_selected($this->ballot->id, $candidate2->id);
      $candidate3_not_selected = $user_ballot_candidate_repository->candidate_is_selected($this->ballot->id, $candidate3->id);

      $this->assertEquals($candidate_selected, true);
      $this->assertEquals($candidate2_not_selected, false);
      $this->assertEquals($candidate3_not_selected, false);
  }

  public function testSelectDifferentCandidate()
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

      factory(\App\DataLayer\UserBallotCandidate::class)->create([
        'user_ballot_id' => $this->ballot->id,
        'candidate_id' => $candidate1->id
      ]);

      $candidate2 = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'office' => 'Senate',
        'office_level' => 'State',
        'is_incumbent' => 1,
        'district_type' => 'State Legislative (Upper)',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->state_legislative_district
      ]);

      $user_ballot_candidate_repository = new UserBallotCandidateRepository();

      // Act
      $response = $this->actingAs($this->user)
        ->put('/api/users/me/ballots/'.$this->ballot->id.'/candidates/' . $candidate2->id);

      // Assert

      $response->assertStatus(201);

      $candidate1_selected = $user_ballot_candidate_repository->candidate_is_selected($this->ballot->id, $candidate1->id);
      $candidate2_selected = $user_ballot_candidate_repository->candidate_is_selected($this->ballot->id, $candidate2->id);

      $this->assertEquals($candidate1_selected, false);
      $this->assertEquals($candidate2_selected, true);
  }

  public function testUnselectCandidate()
  {
      // Arrange
      $candidate = factory(\App\DataLayer\Candidate\Candidate::class)->create([
        'election_id' => $this->election->id,
        'office' => 'Senate',
        'office_level' => 'Federal',
        'is_incumbent' => 1,
        'district_type' => 'Congress',
        'district' => 'Washington DC',
        'district_identifier' => $this->ballot->congressional_district
      ]);

      factory(\App\DataLayer\UserBallotCandidate::class)->create([
        'user_ballot_id' => $this->ballot->id,
        'candidate_id' => $candidate->id
      ]);

      $user_ballot_candidate_repository = new UserBallotCandidateRepository();

      // Act
      $response = $this->actingAs($this->user)
        ->delete('/api/users/me/ballots/'.$this->ballot->id.'/candidates/' . $candidate->id);

      // Assert

      $response->assertStatus(202);

      $candidate_selected = $user_ballot_candidate_repository->candidate_is_selected($this->ballot->id, $candidate->id);

      $this->assertEquals($candidate_selected, false);
  }
}
