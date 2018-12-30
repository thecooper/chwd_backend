<?php

use Faker\Generator as Faker;

$factory->define(\App\DataLayer\Election\Election::class, function (Faker $faker) {
    return [
      // 'consolidated_election_id' => ???,
      'name' => $faker->realText(rand(4,6)),
      'state_abbreviation' => $faker->stateAbbr,
      'primary_election_date' => $faker->date(),
      'general_election_date' => $faker->date(),
      'runoff_election_date' => $faker->date(),
      // 'data_source_id' => ???,
    ];
});
