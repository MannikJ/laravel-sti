<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models\Animals;

use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MannikJ\Laravel\SingleTableInheritance\Tests\Models\Category;

class Animal extends Model
{
    use SingleTableInheritance;

    protected $fillable = ['name'];

    public function resolveTypeViaAttributes($attributes = [])
    {
        if ($category = Category::find(Arr::get($attributes, 'category_id'))) {
            return $category->config_class;
        };
    }

    public function applyTypeCharacteristics($type)
    {
        $this->category_id = Category::where('config_class', $type)->first()->id;
    }

    public function scopeSti(Builder $builder)
    {
        $builder->whereHas('category', function ($query) use ($builder) {
            \Log::debug(get_class($builder->getModel()));
            $query->where('categories.config_class', static::class);
        });
    }

    public function category()
    {
        \Log::debug('category');
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'config_class' => static::class
        ]);
    }
}
