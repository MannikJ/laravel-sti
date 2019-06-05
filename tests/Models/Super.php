<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;
use Illuminate\Database\Eloquent\Builder;

class Super extends Model
{
    use SingleTableInheritance;

    protected $fillable = ['name'];

    public function resolveTypeViaAttributes($attributes = [])
    {
        if ($category = Category::find(array_get($attributes, 'category_id'))) {
            return $category->class_name;
        };
    }

    public function applyTypeCharacteristics($type)
    {
        $this->category_id = Category::where('class_name', $type)->first()->id;
    }

    public static function typeScope(Builder $builder)
    {
        $builder->whereHas('category', function ($query) use ($builder) {
            \Log::debug(get_class($builder->getModel()));
            $query->where('categories.class_name', static::class);
        });
    }

    public function category()
    {
        \Log::debug('category');
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'class_name' => static::class
        ]);
    }
}
