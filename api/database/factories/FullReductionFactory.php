<?php

use App\Models\v1\FullReduction;
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

$factory->define(FullReduction::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'start_time' => now(),
        'end_time' => now()->addDays(7),
        'participation_type' => 1,
        'include_goods_ids' => json_encode([]),
        'exclude_goods_ids' => json_encode([]),
        'include_brands_ids' => json_encode([]),
        'exclude_brands_ids' => json_encode([]),
        'include_categories_ids' => json_encode([]),
        'exclude_categories_ids' => json_encode([]),
        'status' => 1,
        'sort' => 100,
    ];
});