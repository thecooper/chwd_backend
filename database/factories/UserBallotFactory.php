<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\UserBallot::class, function (Faker $faker) {
    return [
      'user_id' => $faker->randomDigit,
      'address_line_1' => $faker->streetAddress,
      'address_line_2' => null,
      'city' => $faker->city,
      'zip' => $faker->postcode,
      'county' => '',
      'state_abbreviation' => $faker->stateAbbr,
      'congressional_district' => null,
      'state_legislative_district' => null,
      'state_house_district' => null
    ];
});
