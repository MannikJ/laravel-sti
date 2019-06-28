<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Unit\Traits;

use MannikJ\Laravel\SingleTableInheritance\Tests\LaravelTest;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\Vehicle;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\Car;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\Plane;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\SUV;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Category;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Animals\Monkey;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Animals\Animal;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Animals\Tiger;

class SingleTableInheritanceTest extends LaravelTest
{
    /** @test */
    public function query_via_root_class_retrieves_all_with_instances_of_subclasses()
    {
        $count = 5;
        $types = [Car::class, Plane::class];
        foreach ($types as $type) {
            $vehicles = factory(Vehicle::class, $count)->states($type)->create();
            $this->assertInstanceOf(Vehicle::class, $vehicles->first());
        }

        $results = Vehicle::all();
        $this->assertEquals(sizeof($types) * $count, $results->count());
        $results->each(function ($vehicle) {
            $this->assertEquals($vehicle->getAttributes()['type'], get_class($vehicle));
        });
    }

    /** @test */
    public function query_via_subclass_returns_only_instances_of_sublass()
    {
        $count = 5;
        $types = [Car::class, Plane::class];
        foreach ($types as $type) {
            factory(Vehicle::class, $count)->states($type)->create();
        }

        foreach ($types as $type) {
            $results = $type::all();
            $countResult = $results->count();
            $this->assertEquals($count, $countResult);
            $this->assertInstanceOf($type, $results->first());
        }
    }

    /** @test */
    public function fresh_returns_instance_of_sublcass_if_exists()
    {
        $vehicle = factory(Vehicle::class)->create();
        $this->assertEquals(Vehicle::class, get_class($vehicle));
        $this->assertEquals(Vehicle::class, get_class($vehicle->fresh()));
        $vehicle = factory(Vehicle::class)->states(Car::class)->create();
        $this->assertEquals(Vehicle::class, get_class($vehicle));
        $this->assertEquals(Car::class, get_class($vehicle->fresh()));
    }

    /** @test */
    public function vehicle_belongs_to_category_category_has_vehicles()
    {
        $category = factory(Category::class)->create();
        $vehicle = $category->vehicles()->create(['name' => 'test']);
        $this->assertTrue($vehicle->category->exists());
        $this->assertTrue($vehicle->category()->get()->contains($category));
        $this->assertInstanceOf(Vehicle::class, $category->vehicles()->first());
    }


    /** @test */
    public function can_resolve_type_from_related_model()
    {
        $category = factory(Category::class)->create(['config_class' => Tiger::class]);
        $super = $category->animals()->create(['name' => 'test']);
        $this->assertTrue($super->category()->get()->contains($category));
        $this->assertInstanceOf(Tiger::class, $category->animals()->first());
        $this->assertTrue($category->is(Tiger::create()->category));
    }


    /** @test */
    public function custom_scope_through_related_model()
    {
        $types = [Tiger::class, Monkey::class];

        foreach ($types as $type) {
            $category = factory(Category::class)->create(['config_class' => $type]);
            $this->assertEquals(get_class(new $type()), $type);
            $this->assertNotNull(Category::where('config_class', Tiger::class)->first());
            $super = $category->animals()->create(['name' => 'test']);
            $this->assertTrue($super->category->exists());
        }

        $this->assertCount(count($types) * 1, Animal::all());

        foreach ($types as $type) {
            $results = $type::all();
            $this->assertCount(1, $results);
            $this->assertInstanceOf($type, $results->first());
        }
    }

    /** @test */
    public function get_sti_type_map()
    {
        $vehicle = factory(Vehicle::class)->create();
        $this->assertArraySubset([Plane::class, Car::class, SUV::class], array_keys($vehicle->getStiTypeMap()));
    }

    /** @test */
    public function can_have_nested_types() {
        $vehicle = factory(Car::class)->create();
        $suv = factory(SUV::class)->create();
        $this->assertEquals(2, Vehicle::count());
        $this->assertEquals(1, SUV::count());
    }

    /** @test */
    public function queries_are_scoped_correctly()
    {
        factory(Vehicle::class, 10)->create();
        factory(Car::class, 10)->create();
        $this->assertCount(20, Vehicle::all());
        $this->assertCount(10, Car::all());
    }
}
