<?php

use Faker\Generator as Faker;

$factory->define(\App\DataSource::class, function (Faker $faker) {
    return [
        'name' => $faker->company
    ];
});
