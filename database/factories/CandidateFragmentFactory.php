<?php

use Faker\Generator as Faker;

$factory->define(\App\DataLayer\Candidate\Candidate::class, function (Faker $faker) {
    return [
      'name' => $faker->name,
      // 'election_id' => null,
      // 'consolidated_candidate_id' => ???,
      // 'data_source_id' => '???',
      'party_affiliation' => $faker->randomElement(['Democratic', 'Republican', 'Libertarian']),
      'election_status' => $faker->randomElement(['On The Ballot', 'Withdrew', 'NULL', 'Disqualified']),
      'office' => $faker->randomElement(['Governor', 'Senate', 'Representative']),
      'office_level' => $faker->randomElement(['State', 'Local', 'Federal']),
      'is_incumbent' => $faker->randomElement([1, 0]),
      'district_type' => $faker->randomElement(['State Legislative (Lower)', 'State Legislative (Upper)', 'County', 'State', 'Judicial District', 'Congress', 'School District']),
      'district' => $faker->state,
      'district_identifier' => $faker->randomDigit,
      'ballotpedia_url' => 'https://www.google.com',
      'website_url' => 'https://www.yahoo.com',
      'donate_url' => 'https://www.redcross.com',
      'facebook_profile' => 'https://www.facebook.com',
      'twitter_handle' => $faker->userName
    ];
});

$factory->afterMaking(\App\DataLayer\Candidate\ConsolidatedCandidate::class, function ($candidate, $faker) {
  factory(\App\DataLayer\Candidate\ConsolidatedCandidate::class, 1)->create([
    'consolidated_candidate_id' => $candidate->id,
    'election_id' => $candidate->election_id
  ]);
});