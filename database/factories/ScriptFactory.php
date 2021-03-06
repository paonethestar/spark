<?php

use Faker\Generator as Faker;
use ProcessMaker\Models\Script;

$factory->define(Script::class, function (Faker $faker) {
    return [
        'key' => null,
        'title' => $faker->sentence,
        'language' => $faker->randomElement(['php', 'lua']),
        'code' => $faker->sentence($faker->randomDigitNotNull),
        'description' => $faker->sentence
    ];
});
