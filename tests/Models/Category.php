<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Animals\Animal;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles\Vehicle;

class Category extends Model
{
    protected $fillable = ['name'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }
}
