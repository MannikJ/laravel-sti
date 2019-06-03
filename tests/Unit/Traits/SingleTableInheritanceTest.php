<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Unit\Traits;

use MannikJ\Laravel\SingleTableInheritance\Tests\LaravelTest;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicle;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Car;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Plane;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Category;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Sub;


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
        $category = factory(Category::class)->create(['class_name' => Sub::class]);
        $super = $category->supers()->create(['name' => 'test']);
        $this->assertTrue($super->category()->get()->contains($category));
        $this->assertInstanceOf(Sub::class, $category->supers()->first());
    }

    /** @test */
    public function custom_scope_through_related_model()
    {
        $category = factory(Category::class)->create(['class_name' => Sub::class]);
        $category = factory(Category::class)->create(['class_name' => Child::class]);
        $super = $category->supers()->create(['name' => 'test']);
        $results = Sub::all();
        $this->assertCount(1, $results);
        $this->assertInstanceOf(Sub::class, $results->first());
    }
}
