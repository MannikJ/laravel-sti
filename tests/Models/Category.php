<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function supers()
    {
        return $this->hasMany(Super::class);
    }
}
