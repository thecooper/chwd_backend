<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\ElectionFragmentCombiner;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\CandidateFragmentCombiner;
use App\BusinessLogic\Repositories\CandidateRepository;
use App\BusinessLogic\Models\Election;

use App\DataSources\FieldMapper;
use App\DataSources\Ballotpedia\BallotpediaDataProcessor;
use App\DataSources\Ballotpedia\DistrictIdentityGenerator;

class BallotpediaDataProcessorTest extends TestCase
{
  private $election_repo;
  private $field_mapper;
  private $candidate_repo;

  public function setUp() {
    parent::setUp();

    $election_fragment_combiner = new ElectionFragmentCombiner();
    $candidate_fragment_combiner = new CandidateFragmentCombiner();

    $this->election_repo = new ElectionRepository($election_fragment_combiner);
    $this->field_mapper = new FieldMapper();
    $this->candidate_repo = new CandidateRepository($candidate_fragment_combiner);
    $this->district_identity_generator = new DistrictIdentityGenerator();
  }

  private function generateInputs() {
    return [
      'AK',
      'Kathryn Dodge',
      'Kathryn',
      'Dodge',
      'https://ballotpedia.org/Kathryn_Dodge',
      '43772',
      'Democratic Party',
      '27534',
      '11/6/18',
      '12/6/18',
      '2622',
      'Alaska House of Representatives District 1',
      'State Legislative (Lower)',
      'State',
      'Alaska House of Representatives District 1',
      'No',
      'On the Ballot',
       '',
      'https://www.facebook.com/alaskansfordodge/',
       ''
    ];
  }

  public function testCanIntantiate() {
    // Arrange/Act
    $processor = new BallotpediaDataProcessor($this->field_mapper, $this->election_repo, $this->candidate_repo, $this->district_identity_generator);
    
    // Assert
    $this->assertNotNull($processor);
  }

  public function testCanInitialize() {
    // Arrange/Act
    $processor = new BallotpediaDataProcessor($this->field_mapper, $this->election_repo, $this->candidate_repo, $this->district_identity_generator);
    
    // Act
    $processor->initialize(1);
    
    // Assert
    $this->assertEquals(true, true);
  }

  public function testCanSaveElection() {
    // Arrange
    $election_repo_mock = $this->createMock('App\BusinessLogic\Repositories\ElectionRepository');
    $candidate_repo_mock = $this->createMock('App\BusinessLogic\Repositories\CandidateRepository');

    $processor = new BallotpediaDataProcessor($this->field_mapper, $election_repo_mock, $candidate_repo_mock, $this->district_identity_generator);

    $election = new Election();
    $election->id = 1;
    
    $election_repo_mock->expects($this->once())
      ->method('save')
      ->with($this->callback(function($entity) {
        // Assert
        if($entity->name != 'Alaska General Election 2018' ||
          $entity->state_abbreviation != 'AK' ||
          $entity->primary_election_date != null ||
          $entity->general_election_date != '2018-11-06' ||
          $entity->runoff_election_date != '2018-12-06') {
          return false;
        } else {
          return true;
        }
        return true;
      },
      $this->greaterThan(0)))
      ->willReturn($election);

    $inputs = $this->generateInputs();

    // Act
    $processor->initialize(1);
    $processor->process_fields($inputs);
  }

  public function testWillNotSaveSameElectionTwice() {
    // Arrange
    $election_repo_mock = $this->createMock('App\BusinessLogic\Repositories\ElectionRepository');
    $candidate_repo_mock = $this->createMock('App\BusinessLogic\Repositories\CandidateRepository');

    $processor = new BallotpediaDataProcessor($this->field_mapper, $election_repo_mock, $candidate_repo_mock, $this->district_identity_generator);

    $election = new Election();
    $election->id = 1;
    
    $election_repo_mock->expects($this->once())
      ->method('save')
      ->with($this->callback(function($entity) {
        // Assert
        if($entity->name != 'Alaska General Election 2018' ||
          $entity->state_abbreviation != 'AK' ||
          $entity->primary_election_date != null ||
          $entity->general_election_date != '2018-11-06' ||
          $entity->runoff_election_date != '2018-12-06') {
          return false;
        } else {
          return true;
        }
        return true;
      },
      $this->greaterThan(0)))
      ->willReturn($election);

    $inputs = $this->generateInputs();

    // Act
    $processor->initialize(1);
    $processor->process_fields($inputs);
    $processor->process_fields($inputs);
  }

  public function testCanSaveCandidate() {
    // Arrange
    $election_repo_mock = $this->createMock('App\BusinessLogic\Repositories\ElectionRepository');
    $candidate_repo_mock = $this->createMock('App\BusinessLogic\Repositories\CandidateRepository');

    $processor = new BallotpediaDataProcessor($this->field_mapper, $election_repo_mock, $candidate_repo_mock, $this->district_identity_generator);

    $election = new Election();
    $election->id = 1;
    
    $election_repo_mock->expects($this->once())
      ->method('save')
      ->willReturn($election);

    $candidate_repo_mock->expects($this->once())
      ->method('save')
      ->with($this->callback(function($entity) {
        // Assert
        if (
          $entity->name != 'Kathryn Dodge' ||
          $entity->party_affiliation != 'Democratic Party' ||
          $entity->election_status != 'On the Ballot' ||
          $entity->office != 'Alaska House of Representatives District 1' ||
          $entity->office_level != 'State' ||
          $entity->is_incumbent != false ||
          $entity->district_type != 'State Legislative (Lower)' ||
          $entity->district != 'Alaska House of Representatives District 1' ||
          $entity->district_identifier != '1' ||
          $entity->ballotpedia_url != 'https://ballotpedia.org/Kathryn_Dodge' ||
          $entity->website_url != '' ||
          $entity->donate_url != '' ||
          $entity->facebook_profile != 'https://www.facebook.com/alaskansfordodge/' ||
          $entity->twitter_handle != ''
        ) {
          return false;
        } else {
          return true;
        }
        return true;
      },
      $this->greaterThan(0)));
      
    $inputs = $this->generateInputs();

    // Act
    $processor->initialize(1);
    $processor->process_fields($inputs);
  }

  public function testCanParseDistrictLetter() {
    // Arrange
    $election_repo_mock = $this->createMock('App\BusinessLogic\Repositories\ElectionRepository');
    $candidate_repo_mock = $this->createMock('App\BusinessLogic\Repositories\CandidateRepository');

    $processor = new BallotpediaDataProcessor($this->field_mapper, $election_repo_mock, $candidate_repo_mock, $this->district_identity_generator);

    $election = new Election();
    $election->id = 1;
    
    $election_repo_mock->expects($this->once())
      ->method('save')
      ->willReturn($election);

    $candidate_repo_mock->expects($this->once())
      ->method('save')
      ->with($this->callback(function($entity) {
        // Assert
        if (
          $entity->name != 'Kathryn Dodge' ||
          $entity->party_affiliation != 'Democratic Party' ||
          $entity->election_status != 'On the Ballot' ||
          $entity->office != 'Alaska House of Representatives District 1' ||
          $entity->office_level != 'State' ||
          $entity->is_incumbent != false ||
          $entity->district_type != 'State Legislative (Lower)' ||
          $entity->district != 'Alaska State Senate District A' ||
          $entity->district_identifier != 'A' ||
          $entity->ballotpedia_url != 'https://ballotpedia.org/Kathryn_Dodge' ||
          $entity->website_url != '' ||
          $entity->donate_url != '' ||
          $entity->facebook_profile != 'https://www.facebook.com/alaskansfordodge/' ||
          $entity->twitter_handle != ''
        ) {
          return false;
        } else {
          return true;
        }
        return true;
      },
      $this->greaterThan(0)));
      
    $inputs = $this->generateInputs();
    $inputs[11] = 'Alaska State Senate District A';

    // Act
    $processor->initialize(1);
    $processor->process_fields($inputs);
  }
}
