<?php

use Faker\Generator as Faker;

$factory->define(\App\DataLayer\Election\Election::class, function (Faker $faker) {
    return [
      // 'election_id' => ???
      'name' => $faker->realText(10),
      'state_abbreviation' => $faker->stateAbbr,
      'primary_election_date' => $faker->date(),
      'general_election_date' => $faker->date(),
      'runoff_election_date' => $faker->date(),
    ];
});

$factory->define(\App\DataLayer\Election\ElectionFragment::class, function (Faker $faker) {
  return [];
});
