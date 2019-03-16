<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\ElectionFragmentCombiner;

use App\DataLayer\Election\Election;
use App\DataLayer\Election\ElectionDTO;
use App\DataLayer\Election\ElectionFragment;

class ElectionRepositoryTest extends TestCase
{
  use RefreshDatabase;

  private $repo;
  
  public function setUp() {
    parent::setUp();

    $election_fragment_combiner = new ElectionFragmentCombiner();
    $this->repo = new ElectionRepository($election_fragment_combiner);
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
    $elections = factory(\App\DataLayer\Election\Election::class, 3)->create();

    $results = $this->repo->all();

    // Assert
    $this->assertEquals(3, count($results));

    $this->assertSameValues($elections[0], $results[0]);
    $this->assertSameValues($elections[1], $results[1]);
    $this->assertSameValues($elections[2], $results[2]);
  }

  public function testCanSaveElection() {
    // Arrange
    $datasource = factory(\App\DataLayer\DataSource\DataSource::class)->create();
    $election_model = factory(\App\DataLayer\Election\Election::class)->make();

    $election = new \App\BusinessLogic\Models\Election();

    ElectionDTO::convert($election_model, $election);

    // Act
    $saved_election = $this->repo->save($election, $datasource->id);
    $fragments = ElectionFragment::where('election_id', $saved_election->id);

    // Assert
    $this->assertNotNull($saved_election->id);
    $this->assertSameValues($election, $saved_election);
    $this->assertEquals(1, $fragments->count());
    $this->assertEquals($saved_election->id, $fragments->first()->election_id);
  }

  public function testCanSaveMultipleElectionFragments() {
    // Arrange
    $datasources = factory(\App\DataLayer\DataSource\DataSource::class, 2)->create();

    $datasource_priorities = factory(\App\DataLayer\DataSource\DataSourcePriority::class, 2)->make([
      'destination_table' => 'elections'
    ]);

    $datasource_priorities[0]->data_source_id = $datasources[0]->id;
    $datasource_priorities[0]->priority = 2;
    $datasource_priorities[0]->save();

    $datasource_priorities[1]->data_source_id = $datasources[1]->id;
    $datasource_priorities[1]->priority = 1;
    $datasource_priorities[1]->save();
    
    // Randomly generate a new Election
    $election_model = factory(\App\DataLayer\Election\Election::class)->make([
      'name' => 'Alaska General Election 2018',
      'state_abbreviation' => 'AK',
      'primary_election_date' => '2018-10-06',
      'general_election_date' => '2018-11-06',
      'runoff_election_date' => '2018-12-06'
    ]);

    $election_fragment = new \App\BusinessLogic\Models\Election();

    // Transfer the generated Election to a Business Logic type
    ElectionDTO::convert($election_model, $election_fragment);
    $election_fragment->id = null;

    $saved_fragment = $this->repo->save($election_fragment, $datasources[0]->id);

    // Create new business model to save
    $election = new \App\BusinessLogic\Models\Election();

    // Update properties on Business model
    $updated_election_name = 'Alaska Primary Election 2018';
    ElectionDTO::convert($election_model, $election);

    $election->name = $updated_election_name;

    // Act
    $saved_election = $this->repo->save($election, $datasources[1]->id);

    // Assert
    $this->assertEquals($updated_election_name, $saved_election->name);
    $this->assertEquals($election_model->general_election_date, $saved_election->general_election_date);
  }

  public function testCanSaveSameElectionFragmentTwice() {
    // Arrange
    $datasource = factory(\App\DataLayer\DataSource\DataSource::class)->create();
    $election_model = factory(\App\DataLayer\Election\Election::class)->make();

    $datasource_priorities = factory(\App\DataLayer\DataSource\DataSourcePriority::class)->create([
      'destination_table' => 'elections',
      'data_source_id' => $datasource->id,
      'priority' => 1
    ]);
    
    $election = new \App\BusinessLogic\Models\Election();

    ElectionDTO::convert($election_model, $election);

    // Act
    $this->repo->save($election, $datasource->id);
    $saved_election = $this->repo->save($election, $datasource->id);
    $fragments = ElectionFragment::where('election_id', $saved_election->id);

    // Assert
    $this->assertEquals(1, $fragments->count());
    $this->assertEquals($saved_election->id, $fragments->first()->election_id);
  }
  
  private function assertSameValues($expected, $actual) {
    $this->assertEquals($expected->name, $actual->name);
    $this->assertEquals($expected->state_abbreviation, $actual->state_abbreviation);
    $this->assertEquals($expected->primary_election_date, $actual->primary_election_date);
    $this->assertEquals($expected->general_election_date, $actual->general_election_date);
    $this->assertEquals($expected->runoff_election_date, $actual->runoff_election_date);
  }
}
