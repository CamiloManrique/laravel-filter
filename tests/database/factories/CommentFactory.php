<?php

use Faker\Generator as Faker;
use CamiloManrique\Filter\Tests\Models\Comment;

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

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'comment' => $faker->text(),
        'votes' => $faker->numberBetween(-10, 10),
    ];
});
