<?php

use Faker\Generator as Faker;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Category;
use Illuminate\Support\Str;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->name()
    ];
});

