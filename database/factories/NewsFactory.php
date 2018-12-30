<?php

use Faker\Generator as Faker;

$factory->define(\App\DataLayer\News::class, function (Faker $faker) {
    return [
      'url' => $faker->url,
      'thumbnail_url' => $faker->imageUrl,
      'title' => $faker->sentence(),
      'description' => $faker->paragraph(),
      'publish_date' => $faker->date('Y-m-d'),
      // 'candidate_id' => '',
    ];
});
