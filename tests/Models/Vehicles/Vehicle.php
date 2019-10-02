<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models\Vehicles;

use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Category;

class Vehicle extends Model
{
    use SingleTableInheritance;

    protected static $stiSubClasses = [
        Plane::class,
        Car::class,
    ];

    protected $fillable = ['name'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'config_class' => static::class
        ]);
    }
}
