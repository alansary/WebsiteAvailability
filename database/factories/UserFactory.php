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

$factory->define(App\User::class, function (Faker $faker) {
	$password = \Hash::make(str_random(10));
    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => \Hash::make(str_random(10))
    ];
});
