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

$factory->define(App\Url::class, function (Faker $faker) {
    return [
        'url' => $faker->url,
        'user_id' => function () {
            return App\User::pluck('id')->random() ?: factory(App\User::class)->create()->id;
        },
        'is_active' => array_rand(array(0, 1))
    ];
});
