<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\Repositories\CandidateRepository;
use App\BusinessLogic\CandidateFragmentCombiner;
use App\BusinessLogic\Validation\CandidateValidation;

use App\DataLayer\Candidate\Candidate;
use App\DataLayer\Candidate\CandidateDTO;
use App\DataLayer\Candidate\CandidateFragment;

class CandidateRepositoryTest extends TestCase
{
  use RefreshDatabase;

  private $election;
  private $datasource;
  private $repo;
  
  public function setUp() {
    parent::setUp();

    $this->election = factory(\App\DataLayer\Election\Election::class)->create();
    $this->datasource = factory(\App\DataLayer\DataSource\DataSource::class)->create();
    $this->repo = new CandidateRepository(new CandidateFragmentCombiner(), new CandidateValidation());
  }

  /**
   * A basic test example.
   *
   * @return void
   */
  public function testCanInitialize()
  {
      // Assert
      $this->assertNotNull($this->repo);
  }

  public function testCanGetAll() {
    // Arrange
    $candidates = factory(\App\DataLayer\Candidate\Candidate::class, 3)->create([
      'election_id' => $this->election->id
    ]);
    
    $election = $this->election;
    $datasource = $this->datasource;
      
    $candidates->each(function($candidate) use ($election, $datasource) {
      $candidate_fragment = factory(\App\DataLayer\Candidate\CandidateFragment::class)->make([
        'election_id' => $election->id,
        'data_source_id' => $datasource->id
      ]);

      CandidateDTO::convert($candidate, $candidate_fragment);

      $candidate_fragment->save();
    });

    // Act
    $results = $this->repo->all();

    // Assert
    $this->assertEquals(count($results), 3);

    $this->assertSameValues($candidates[0], $results[0]);
    $this->assertSameValues($candidates[1], $results[1]);
    $this->assertSameValues($candidates[2], $results[2]);
  }

  public function testCanSaveCandidate() {
    // Arrange

    $candidate_model = factory(\App\DataLayer\Candidate\Candidate::class)->make([
      'election_id' => $this->election->id
    ]);

    $candidate = new \App\BusinessLogic\Models\Candidate();

    CandidateDTO::convert($candidate_model, $candidate);

    // Act
    $saved_candidate = $this->repo->save($candidate, $this->datasource->id);
    $fragments = \App\DataLayer\Candidate\CandidateFragment::all(); // ('candidate_id', $saved_candidate->id);

    // Assert
    $this->assertNotNull($saved_candidate->id);
    $this->assertSameValues($candidate, $saved_candidate);
    $this->assertEquals(1, $fragments->count());
    $this->assertEquals($saved_candidate->id, $fragments->first()->candidate_id);
  }

  public function testCanSaveMultipleCandidateFragments() {
    // Arrange

    $datasource2 = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $datasource_priorities = factory(\App\DataLayer\DataSource\DataSourcePriority::class, 2)->make([
      'destination_table' => 'candidates'
    ]);

    $datasource_priorities[0]->data_source_id = $this->datasource->id;
    $datasource_priorities[0]->priority = 2;
    $datasource_priorities[0]->save();

    $datasource_priorities[1]->data_source_id = $datasource2->id;
    $datasource_priorities[1]->priority = 1;
    $datasource_priorities[1]->save();
    
    $candidate_model = factory(\App\DataLayer\Candidate\Candidate::class)->create([
      'election_id' => $this->election->id
    ]);

    $candidate_fragment = factory(\App\DataLayer\Candidate\CandidateFragment::class)->make();

    CandidateDTO::convert($candidate_model, $candidate_fragment);
    $candidate_fragment->data_source_id = $this->datasource->id;
    dd($candidate_fragment);
    $candidate_fragment->save();
    
    $candidate = new \App\BusinessLogic\Models\Candidate();

    CandidateDTO::convert($candidate_model, $candidate);
    $candidate->id = null;
    $candidate->donate_url = 'https://www.yahoo.com';
    $candidate->facebook_profile = null;

    // Act
    $saved_candidate = $this->repo->save($candidate, $datasource2->id);

    // Assert
    $this->assertEquals('https://www.yahoo.com', $saved_candidate->donate_url);
    $this->assertEquals($candidate_model->facebook_profile, $saved_candidate->facebook_profile);
  }

  public function testCanSaveCandidateFragmentTwice() {
    // Arrange
    $datasource_priorities = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'destination_table' => 'candidates',
      'data_source_id' => $this->datasource->id,
      'priority' => 1
    ]);

    $candidate_model = factory(\App\DataLayer\Candidate\Candidate::class)->make([
      'election_id' => $this->election->id
    ]);

    $candidate = new \App\BusinessLogic\Models\Candidate();

    CandidateDTO::convert($candidate_model, $candidate);
    $candidate = $this->repo->save($candidate, $this->datasource->id);
    $candidate->election_status = 'Lost';
    
    // Act
    $saved_candidate = $this->repo->save($candidate, $this->datasource->id);
    $candidate_fragments = \App\DataLayer\Candidate\CandidateFragment::where('candidate_id', $saved_candidate->id);

    // Assert
    $this->assertNotNull($saved_candidate->id);
    $this->assertEquals(1, $candidate_fragments->count());
    $this->assertEquals('Lost', $candidate_fragments->first()->election_status);
  }

  private function assertSameValues($expected, $actual) {
    $this->assertEquals($expected->name, $actual->name, "Expected that property name would have value {$expected->name}, but instead got value {$actual->name}");
    $this->assertEquals($expected->party_affiliation, $actual->party_affiliation, "Expected that property party_affiliation would have value {$expected->party_affiliation}, but instead got value {$actual->party_affiliation}");
    $this->assertEquals($expected->election_status, $actual->election_status, "Expected that property election_status would have value {$expected->election_status}, but instead got value {$actual->election_status}");
    $this->assertEquals($expected->office, $actual->office, "Expected that property office would have value {$expected->office}, but instead got value {$actual->office}");
    $this->assertEquals($expected->office_level, $actual->office_level, "Expected that property office_level would have value {$expected->office_level}, but instead got value {$actual->office_level}");
    $this->assertEquals($expected->is_incumbent, $actual->is_incumbent, "Expected that property is_incumbent would have value {$expected->is_incumbent}, but instead got value {$actual->is_incumbent}");
    $this->assertEquals($expected->district_type, $actual->district_type, "Expected that property district_type would have value {$expected->district_type}, but instead got value {$actual->district_type}");
    $this->assertEquals($expected->district, $actual->district, "Expected that property district would have value {$expected->district}, but instead got value {$actual->district}");
    $this->assertEquals($expected->district_identifier, $actual->district_identifier, "Expected that property district_identifier would have value {$expected->district_identifier}, but instead got value {$actual->district_identifier}");
    $this->assertEquals($expected->ballotpedia_url, $actual->ballotpedia_url, "Expected that property ballotpedia_url would have value {$expected->ballotpedia_url}, but instead got value {$actual->ballotpedia_url}");
    $this->assertEquals($expected->website_url, $actual->website_url, "Expected that property website_url would have value {$expected->website_url}, but instead got value {$actual->website_url}");
    $this->assertEquals($expected->donate_url, $actual->donate_url, "Expected that property donate_url would have value {$expected->donate_url}, but instead got value {$actual->donate_url}");
    $this->assertEquals($expected->facebook_profile, $actual->facebook_profile, "Expected that property facebook_profile would have value {$expected->facebook_profile}, but instead got value {$actual->facebook_profile}");
    $this->assertEquals($expected->twitter_handle, $actual->twitter_handle, "Expected that property twitter_handle would have value {$expected->twitter_handle}, but instead got value {$actual->twitter_handle}");
    $this->assertEquals($expected->election_id, $actual->election_id, "Expected that property election_id would have value {$expected->election_id}, but instead got value {$actual->election_id}");
  }
}
