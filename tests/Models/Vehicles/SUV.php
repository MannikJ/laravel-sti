<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles;

class SUV extends Car
{
    protected static $stiSubClasses = [RacingTruck::class];
}

