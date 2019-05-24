<?php

use Faker\Generator as Faker;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicle;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Plane;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Car;
use Illuminate\Support\Str;

$factory->define(Vehicle::class, function (Faker $faker) {
    return [
        'name' => $faker->name(),
    ];
});

$factory->afterCreating(Vehicle::class, function ($vehicle) {
    return $vehicle = $vehicle->fresh();
});

$subclasses = [
    Car::class,
    Plane::class
];

$factory->state(Vehicle::class, 'random-type', function ($faker) use ($subclasses) {
    return [
        'type' => $faker->randomElement($subclasses)
    ];
});

foreach ($subclasses as $subclass) {
    \Log::debug("FOREACH");
    \Log::debug($subclass);
    $function = function (Faker $faker) use ($subclass) {
        \Log::debug($subclass);
        return ['type' => $subclass];
    };

    $factory->state(Vehicle::class, $subclass, $function);
    $factory->state(Vehicle::class, Str::kebab(class_basename($subclass)), $function);
    $factory->define($subclass, $function);
}
