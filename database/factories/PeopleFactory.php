<?php

use Faker\Generator as Faker;

$factory->define(App\People::class, function (Faker $faker) {
    return [
        'first_name' => $faker->text(20),
        'last_name' => $faker->text(20),
        'birthday' => $faker->date('Y-m-d', 'now')
    ];
});
