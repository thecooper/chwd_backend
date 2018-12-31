<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\DataLayer\Election\Election;
use App\DataLayer\Candidate\Candidate;

class BallotNews extends TestCase
{
  use RefreshDatabase;

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

    $datasource = factory(\App\DataLayer\DataSource\DataSource::class)->create();

    $election = Election::createOrUpdate([
      'name'=>'Some State Election',
      'state_abbreviation'=>$ballot->state_abbreviation,
      'primary_election_date'=>'2018-11-6',
      'general_election_date'=>'2018-11-7',
      'runoff_election_date'=>'2018-11-8',
      'data_source_id'=>$datasource->id,
      'consolidated_election_id'=>null
    ]);

    $candidate = Candidate::createOrUpdate([
      'name' => 'John Doe',
      'election_id' => $election->consolidated_election_id,
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
      'election_id' => $election->consolidated_election_id,
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
}
