<?php

use Faker\Generator as Faker;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\Vehicle;
use Illuminate\Support\Str;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\SUV;

$factory->define(Vehicle::class, function (Faker $faker) {
    return [
        'name' => $faker->name(),
    ];
});

$subclasses = Vehicle::getStiSubClasses();

$factory->state(Vehicle::class, 'random-type', function ($faker) use ($subclasses) {
    return [
        'type' => $faker->randomElement($subclasses + [null])
    ];
});

foreach ($subclasses as $subclass) {
    $function = function (Faker $faker) use ($subclass) {
        return [
            'type' => $subclass,
            'name' => $faker->name(),
        ];
    };

    $factory->state(Vehicle::class, $subclass, $function);
    $factory->state(Vehicle::class, Str::kebab(class_basename($subclass)), $function);
    $factory->define($subclass, $function);
}
