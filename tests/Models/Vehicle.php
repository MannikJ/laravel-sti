<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;

class Vehicle extends Model
{
    use SingleTableInheritance;

    protected $fillable = ['name'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'class_name' => static::class
        ]);
    }
}
