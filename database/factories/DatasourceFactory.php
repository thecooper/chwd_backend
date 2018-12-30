<?php

use Faker\Generator as Faker;

$factory->define(\App\DataLayer\DataSource\DataSource::class, function (Faker $faker) {
    return [
        'name' => $faker->company
    ];
});
