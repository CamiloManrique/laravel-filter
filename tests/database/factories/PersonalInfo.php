<?php

use Faker\Generator as Faker;

use CamiloManrique\LaravelFilter\Tests\Models\PersonalInfo;

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

$factory->define(PersonalInfo::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName,
        'phone' => $faker->phoneNumber,
    ];
});