<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserBallotElection extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetUserBallotElections()
    {
      // Arrange
      $user = factory(\App\User::class)->create();
      $ballot = factory(\App\UserBallot::class)->create();
      $datasource = factory(\App\DataSource::class)->create();
      $election = factory(\App\Models\Election\Election::class)->create([
        'data_source_id' => $datasource->id,
        ''
      ]);

      // Act


      // Assert

    }
}
