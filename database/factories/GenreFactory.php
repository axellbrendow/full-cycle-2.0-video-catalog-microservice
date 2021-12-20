<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Genre;
use Faker\Generator as Faker;

$factory->define(Genre::class, function (Faker $faker) {
    return [
        'name' => $faker->randomElement(['terror', 'action', 'comedy', 'romance', 'drama', 'fantasy'])
    ];
});
