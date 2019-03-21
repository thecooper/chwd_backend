<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\BallotCandidateFilter;
use App\BusinessLogic\Models\Candidate;

class BallotCandidateFilterTest extends TestCase
{
  protected function tearDown() {
    gc_collect_cycles();
  }
  
    public function testCanGetWinners()
    {
      // Arrange
      $filter = new BallotCandidateFilter();
      $candidate1 = new Candidate();
      $candidate1->name = 'John Snow';
      $candidate1->election_status = 'Won';
      $candidate2 = new Candidate();
      $candidate2->name = 'Sir Alliser';
      $candidate2->election_status = 'Lost';
      
      $candidates = [$candidate1, $candidate2];

      // Act
      $results = $filter->get_winner_candidates($candidates);

      // Assert
      $this->assertEquals(1, count($results));
      $this->assertEquals($candidate1, $results[0]);
    }

    public function testGetWinnersReturnsEmptyArray()
    {
      // Arrange
      $filter = new BallotCandidateFilter();
      $candidate1 = new Candidate();
      $candidate1->name = 'John Snow';
      $candidate1->election_status = 'On the Ballot';
      $candidate2 = new Candidate();
      $candidate2->name = 'Sir Alliser';
      $candidate2->election_status = 'Lost';
      
      $candidates = [$candidate1, $candidate2];

      // Act
      $results = $filter->get_winner_candidates($candidates);

      // Assert
      $this->assertEquals(0, count($results));
    }
}
