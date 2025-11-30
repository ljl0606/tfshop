<?php

use App\Models\v1\FullReductionTier;
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

$factory->define(FullReductionTier::class, function (Faker $faker) {
    return [
        'full_amount' => $faker->randomNumber(4) * 100, // 随机金额，单位分
        'reduce_amount' => $faker->randomNumber(3) * 100, // 随机减免金额，单位分
        'sort' => 100,
    ];
});